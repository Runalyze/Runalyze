<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20171217215801 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface|null */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

        $this->addSql("UPDATE `".$prefix."sport` SET default_privacy=1 WHERE accountid IN  (select accountid from `".$prefix."conf` WHERE `key`='TRAINING_MAKE_PUBLIC' and value='false')");
        $this->addSql("UPDATE `".$prefix."sport` SET default_privacy=0 WHERE accountid IN  (select accountid from `".$prefix."conf` WHERE `key`='TRAINING_MAKE_PUBLIC' and value='true')");
        $this->addSql("DELETE FROM `".$prefix."conf` WHERE  `key`='TRAINING_MAKE_PUBLIC'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

    }
}
