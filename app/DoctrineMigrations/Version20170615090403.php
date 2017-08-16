<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\ClimbScoreCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\FlatOrHillyAnalyzer;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Service\ElevationCorrection\StepwiseElevationProfileFixer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20170615090403 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface|null */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function isTransactional()
    {
        return false;
    }

    public function up(Schema $schema)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $repo = $em->getRepository('CoreBundle:Training');
        $numLocked = $this->getNumberOfLockedRoutes($em);

        $flatOrHillyAnalyzer = new FlatOrHillyAnalyzer();
        $climbScoreCalculator = new ClimbScoreCalculator();

        while ($numLocked > 0) {
            $activities = $repo->createQueryBuilder('t')
                ->select('partial t.{id, climbScore, percentageHilly, route}')
                ->addSelect('partial r.{id, lock, distance, elevationsOriginal, elevationsCorrected}')
                ->addSelect('partial tr.{activity, distance}')
                ->join('t.route', 'r')
                ->join('t.trackdata', 'tr')
                ->where('r.lock = 1')
                ->setMaxResults(100)
                ->getQuery();

            $batchSize = 100;
            $i = 0;
            $iterableResult = $activities->iterate();

            foreach ($iterableResult as $row) {
                /** @var Training $activity */
                $activity = $row[0];

                $this->correctSizeOfCorrectedElevationsIfRequired($activity);

                $oldElevations = $activity->getRoute()->getElevationsCorrected();

                if (null !== $activity->getRoute()->getElevationsCorrected() && null !== $activity->getTrackdata()->getDistance()) {
                    $activity->getRoute()->setElevationsCorrected((new StepwiseElevationProfileFixer(
                        5, StepwiseElevationProfileFixer::METHOD_VARIABLE_GROUP_SIZE
                    ))->fixStepwiseElevations(
                        $activity->getRoute()->getElevationsCorrected(),
                        $activity->getTrackdata()->getDistance()
                    ));
                }

                try {
                    $flatOrHillyAnalyzer->calculatePercentageHillyFor($activity);
                    $climbScoreCalculator->calculateFor($activity);
                } catch (\InvalidArgumentException $e) {
                    $this->write(sprintf(
                        '    <comment>Warning: Activity #%u failed. (%s)</comment>',
                        $activity->getId(),
                        $e->getMessage()
                    ));
                };

                $activity->getRoute()->setElevationsCorrected($oldElevations);
                $activity->getRoute()->setLock(false);

                $em->persist($activity);

                if (($i % $batchSize) === 0) {
                    $em->flush();
                    $em->clear();
                    gc_collect_cycles();
                }

                ++$i;
            }

            $em->flush();

            $numLocked = $numLocked - 100;
        }
    }

    /**
     * @param EntityManager $em
     * @return int
     */
    protected function getNumberOfLockedRoutes(EntityManager $em)
    {
        return (int)$em->createQueryBuilder()
            ->select('count(r.id)')
            ->where('r.lock = 1')
            ->from('CoreBundle:Route','r')
            ->getQuery()->getSingleScalarResult();
    }

    protected function correctSizeOfCorrectedElevationsIfRequired(Training $activity)
    {
        if (
            $activity->hasRoute() && null !== $activity->getRoute()->getElevationsCorrected() &&
            $activity->hasTrackdata() && null !== $activity->getTrackdata()->getDistance()
        ) {
            $numDistance = count($activity->getTrackdata()->getDistance());
            $numElevations = count($activity->getRoute()->getElevationsCorrected());

            if ($numElevations > $numDistance) {
                $activity->getRoute()->setElevationsCorrected(array_slice($activity->getRoute()->getElevationsCorrected(), 0, $numDistance));
            }
        }
    }

    public function down(Schema $schema)
    {
    }
}
