<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;
use Runalyze\Parser\Activity\Common\Filter\NegativeDistanceStepFilter;
use Runalyze\Parser\Activity\Common\Filter\NegativeTimeStepFilter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20171104230424 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var bool */
    protected $LogFixes = false;

    /** @var NegativeTimeStepFilter */
    protected $NegativeTimeStepFilter;

    /** @var NegativeDistanceStepFilter */
    protected $NegativeDistanceStepFilter;

    public function isTransactional()
    {
        return false;
    }

    public function up(Schema $schema)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->NegativeTimeStepFilter = new NegativeTimeStepFilter();
        $this->NegativeDistanceStepFilter = new NegativeDistanceStepFilter(0.1);

        $repo = $em->getRepository('CoreBundle:Training');
        $numLocked = $this->getNumberOfLockedRoutes($em) + $this->getNumberOfLockedTrackData($em);

        while ($numLocked > 0) {
            $activities = $repo->createQueryBuilder('t')
                ->select('partial t.{id, route}')
                ->addSelect('partial r.{id, lock, elevationsCorrected}')
                ->addSelect('partial tr.{activity, lock, time, distance, cadence, heartrate}')
                ->join('t.route', 'r')
                ->join('t.trackdata', 'tr')
                ->where('r.lock = 1')
                ->orWhere('t.lock = 1')
                ->setMaxResults(50)
                ->getQuery();

            $batchSize = 50;
            $i = 0;
            $iterableResult = $activities->iterate();

            foreach ($iterableResult as $row) {
                /** @var Training $activity */
                $activity = $row[0];

                if ($activity->hasTrackdata() && ($activity->getTrackdata()->hasTime() || $activity->getTrackdata()->hasDistance())) {
                    $this->fixActivity($activity->getTrackdata(), $activity->getRoute());
                }

                $activity->getRoute()->setLock(false);
                $activity->getTrackdata()->setLock(false);

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

    protected function fixActivity(Trackdata $trackData, Route $route = null)
    {
        $expectedSize = $trackData->hasTime() ? count($trackData->getTime()) : count($trackData->getDistance());

        if ($trackData->hasTime()) {
            if (min($trackData->getTime()) < 0) {
                $this->logMessage(sprintf('#%u: time array removed (negative time).', $trackData->getActivity()->getId()));
                $trackData->setTime(null);
            } else {
                $dataContainer = new ActivityDataContainer();
                $dataContainer->ContinuousData->Time = $trackData->getTime();

                try {
                    $this->NegativeTimeStepFilter->filter($dataContainer, true);
                } catch (InvalidDataException $e) {
                    $this->logMessage(sprintf('#%u: time array removed (negative time step).', $trackData->getActivity()->getId()));
                    $trackData->setTime(null);
                }
            }
        }

        if ($trackData->hasDistance()) {
            $distance = $trackData->getDistance();
            $thereWasAFix = false;

            foreach ($distance as $i => $dist) {
                if ($i > 0 && $dist == 0.0 && $distance[$i - 1] > 0.0) {
                    $thereWasAFix = true;
                    $distance[$i] = $distance[$i - 1];
                }
            }

            if ($thereWasAFix) {
                $this->logMessage(sprintf('#%u: distance array fixed (empty distance).', $trackData->getActivity()->getId()));
                $trackData->setDistance($distance);
            }

            $dataContainer = new ActivityDataContainer();
            $dataContainer->ContinuousData->Distance = $distance;

            try {
                $this->NegativeDistanceStepFilter->filter($dataContainer, false);

                if ($distance != $dataContainer->ContinuousData->Distance) {
                    $trackData->setDistance($dataContainer->ContinuousData->Distance);
                    $this->logMessage(sprintf('#%u: distance array fixed (negative distance step).', $trackData->getActivity()->getId()));
                }
            } catch (InvalidDataException $e) {
                $this->logMessage(sprintf('#%u: distance array removed (negative distance step).', $trackData->getActivity()->getId()));
                $trackData->setDistance(null);
            }
        }

        if ($trackData->hasCadence()) {
            $numCadence = count($trackData->getCadence());

            if ($numCadence != $expectedSize) {
                $cadence = $trackData->getCadence();

                if ($numCadence == $expectedSize + 1) {
                    $cadence = array_slice($cadence, 1);
                    $this->logMessage(sprintf('#%u: cadence array fixed.', $trackData->getActivity()->getId()));
                } elseif ($numCadence == $expectedSize - 1) {
                    array_unshift($cadence, $cadence[0]);
                    $this->logMessage(sprintf('#%u: cadence array fixed.', $trackData->getActivity()->getId()));
                } else {
                    $cadence = null;
                    $this->logMessage(sprintf('#%u: cadence array removed.', $trackData->getActivity()->getId()));
                }

                $trackData->setCadence($cadence);
            }
        }

        if ($trackData->hasHeartrate()) {
            $numHeartRate = count($trackData->getHeartrate());

            if ($numHeartRate != $expectedSize) {
                $heartRate = $trackData->getHeartrate();

                if ($numHeartRate == $expectedSize + 1) {
                    $heartRate = array_slice($heartRate, 1);
                    $this->logMessage(sprintf('#%u: heart rate array fixed.', $trackData->getActivity()->getId()));
                } elseif ($numHeartRate == $expectedSize - 1) {
                    array_unshift($heartRate, $heartRate[0]);
                    $this->logMessage(sprintf('#%u: heart rate array fixed.', $trackData->getActivity()->getId()));
                } else {
                    $heartRate = null;
                    $this->logMessage(sprintf('#%u: heart rate array removed.', $trackData->getActivity()->getId()));
                }

                $trackData->setHeartrate($heartRate);
            }
        }

        if (null !== $route && $route->hasElevations()) {
            $elevationsCorrected = $route->getElevationsCorrected();

            if (null !== $elevationsCorrected) {
                $numElevations = count($elevationsCorrected);

                if ($numElevations > $expectedSize) {
                    $this->logMessage(sprintf('#%u: elevations array fixed.', $trackData->getActivity()->getId()));
                    $route->setElevationsCorrected(array_slice($elevationsCorrected, 0, $expectedSize));
                } elseif ($numElevations < $expectedSize) {
                    $this->logMessage(sprintf('#%u: elevations array removed.', $trackData->getActivity()->getId()));
                    $route->setElevationsCorrected(null);
                }
            }
        }
    }

    /**
     * @param string $message
     */
    protected function logMessage($message)
    {
        if ($this->LogFixes) {
            $this->write($message);
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

    /**
     * @param EntityManager $em
     * @return int
     */
    protected function getNumberOfLockedTrackData(EntityManager $em)
    {
        return (int)$em->createQueryBuilder()
            ->select('count(t.activity)')
            ->where('t.lock = 1')
            ->from('CoreBundle:Trackdata','t')
            ->getQuery()->getSingleScalarResult();
    }

    public function down(Schema $schema)
    {
    }
}
