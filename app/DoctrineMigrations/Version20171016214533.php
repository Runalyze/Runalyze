<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Runalyze\Calculation\Route\GeohashLine;
use Runalyze\Calculation\Activity\TimeArrayMinifier;

/**
 * refactoring time in trackdata table
 */
class Version20171016214533 extends AbstractMigration implements ContainerAwareInterface
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

        $repo = $em->getRepository('CoreBundle:Trackdata');

        $numberLockedTrackdata = $em->createQueryBuilder()
            ->select('count(trackdata.activity)')
            ->where('trackdata.lock = 1')
            ->from('CoreBundle:Trackdata','trackdata')
            ->getQuery()->getSingleScalarResult();

        while ($numberLockedTrackdata > 0) {
            $lockedTrackdata = $repo->createQueryBuilder('r')
                ->select('r')
                ->where('r.lock = 1')
                ->setMaxResults(100)
                ->getQuery();

            $batchSize = 100;
            $i = 0;
            $iterableResult = $lockedTrackdata->iterate();

            foreach ($iterableResult as $row) {
                /** @var Trackdata $trackdata */
                $trackdata = $row[0];
                $trackdata->setLock(0);
                if ( $trackdata->getTime() !== null && $trackdata->getTime() != '') {
                    $trackdata->setTime( TimeArrayMinifier::shorten($trackdata->getTime()) );
                }
                $em->persist($trackdata);

                if (($i % $batchSize) === 0) {
                    $em->flush();
                    $em->clear();
                    gc_collect_cycles();
                }

                ++$i;
            }

            $em->flush();

            $numberLockedTrackdata = $numberLockedTrackdata - 100;
        }
    }

    public function down(Schema $schema)
    {
    }
}
