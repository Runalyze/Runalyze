<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
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

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $prefix = $this->container->getParameter('database_prefix');
        /** @var EntityManager $em */
        $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Route');

        $lockedRoutes = $repo->createQueryBuilder('r')
            ->select('r')
            ->where('r.lock = 1')
            ->getQuery();

        $batchSize = 20;
        $i = 0;
        foreach ($lockedRoutes->iterate() as $row) {
            $route = $row[0];
            $route->setLock(0);
            $route->setGeohashes( implode('|', GeohashLine::shorten( explode('|', $route->getGeohashes()) )) );
            $em->persist($route);
            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear();
            }
            ++$i;
        }
        $em->flush();

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}
