<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Profile\Sport\SportProfile;

class Version20170328101219 extends AbstractMigration implements ContainerAwareInterface
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
        $this->addSql('ALTER TABLE `'.$prefix.'sport` ADD `internal_sport_id` TINYINT NULL AFTER `default_typeid`, ADD `is_main` tinyint(1) unsigned NOT NULL DEFAULT 0 AFTER `default_typeid`');

        $this->addSql('UPDATE `'.$prefix.'sport` s LEFT JOIN `'.$prefix.'conf` c ON s.accountid=c.accountid AND c.`key`="RUNNINGSPORT" SET internal_sport_id='.SportProfile::RUNNING.', is_main=1 WHERE s.id=c.value');
        $this->addSql('ALTER TABLE `'.$prefix.'sport` ADD CONSTRAINT `unique_internal_id` UNIQUE (`accountid`,`internal_sport_id`)');

        $this->addSql('UPDATE IGNORE `'.$prefix.'sport` SET internal_sport_id='.SportProfile::SWIMMING.' WHERE `name` IN ("Schwimmen", "Swimming", "Nuoto", "Zwemmen", "Pływanie", "Natación", "Natation", "Natació")');
        $this->addSql('UPDATE IGNORE `'.$prefix.'sport` SET internal_sport_id='.SportProfile::CYCLING.' WHERE `name` IN ("Biking","Radfahren", "Rennrad", "Cyclisme", "Ciclisme", "Ciclismo", "Bici", "Rolle", "Mountainbike", "Mountainbiken", "MTB", "Indoor Cycling", "Spinning")');
        $this->addSql('UPDATE IGNORE `'.$prefix.'sport` SET internal_sport_id='.SportProfile::ROWING.' WHERE `name` IN ("Rower", "Rudern")');
        $this->addSql('UPDATE IGNORE `'.$prefix.'sport` SET internal_sport_id='.SportProfile::HIKING.' WHERE `name` IN ("Hiking", "Wandern)');

        //Running: Hardlopen, Laufen, Laufband, Course à pied, Corsa, córrer, Bieganie, Carrera a pie
        //Gymnastik: Gimnasia, Gimnàstica, Gimnastyka, Ginnastica, Gymnastics, Gymnastiek, Gymnastik, Gymnastique
        //SKIING - Ski, Skifahren, Skiing, Skitour
        //CROSS_COUNTRY_SKIING - Skilanglauf,Langlaufen,Langlauf
        // Tennis
        //? Walken, Walking, Trekking, Spazieren, Skaten,Klettern, Kajak, Inline, Gehen,Bergwandern, Bergsteigen
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'sport` DROP INDEX `unique_internal_id`');
        $this->addSql('ALTER TABLE `'.$prefix.'sport` DROP COLUMN `internal_sport_id`, DROP COLUMN `is_main`');
    }
}
