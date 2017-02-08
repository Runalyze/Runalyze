<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Runalyze\Calculation\Route\GeohashLine;

/**
 * refactoring geohashes in route table
 */
class Version20161224173160 extends AbstractMigration implements ContainerAwareInterface
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

        $repo = $em->getRepository('CoreBundle:Route');

        $numberLockedRoutes = $em->createQueryBuilder()
            ->select('count(route.id)')
            ->where('route.lock = 1')
            ->from('CoreBundle:Route','route')
            ->getQuery()->getSingleScalarResult();

        while ($numberLockedRoutes > 0) {
            $lockedRoutes = $repo->createQueryBuilder('r')
                ->select('r')
                ->where('r.lock = 1')
                ->setMaxResults(100)
                ->getQuery();

            $batchSize = 100;
            $i = 0;
            $iterableResult = $lockedRoutes->iterate();

            foreach ($iterableResult as $row) {
                /** @var Route $route */
                $route = $row[0];
                $route->setLock(0);
                $route->setGeohashes( implode('|', GeohashLine::shorten( explode('|', $route->getGeohashes()) )) );
                $em->persist($route);

                if (($i % $batchSize) === 0) {
                    $em->flush();
                    $em->clear();
                    gc_collect_cycles();
                }

                ++$i;
            }

            $em->flush();

            $numberLockedRoutes = $numberLockedRoutes - 100;
        }
    }

    public function down(Schema $schema)
    {
    }
}
