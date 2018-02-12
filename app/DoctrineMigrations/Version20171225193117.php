<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20171225193117 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('ALTER TABLE `'.$prefix.'raceresult` CHANGE `official_distance` `official_distance` DECIMAL(6,2) NULL DEFAULT NULL');
        $this->addSql('UPDATE `'.$prefix.'raceresult` SET `official_distance` = NULL WHERE `official_distance` = 0.0');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

        $this->addSql('UPDATE `'.$prefix.'raceresult` SET `official_distance` = 0.0 WHERE `official_distance` IS NULL');
        $this->addSql('ALTER TABLE `'.$prefix.'raceresult` CHANGE `official_distance` `official_distance` DECIMAL(6,2) NOT NULL');
    }
}
