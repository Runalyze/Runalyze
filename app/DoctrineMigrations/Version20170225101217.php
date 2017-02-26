<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20170225101217 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('DELETE FROM `'.$prefix.'plugin_conf` WHERE `config`="show_trainingpaces" OR `config`="show_jd_intensity" OR `config`="model-jd" OR `config`="model-cpp" OR `config`="model-steffny" OR `config`="model-cameron"');
        $this->addSql('UPDATE `'.$prefix.'plugin_conf` SET `config`="show_vo2max" WHERE `config`="show_vdot"');
        $this->addSql('UPDATE `'.$prefix.'plugin_conf` SET `value`="vo2max" WHERE `config`="model" AND `value`="jd"');

        $this->addSql('UPDATE `'.$prefix.'conf` SET `category`="vo2max" WHERE `category`="vdot"');
        $this->addSql('UPDATE `'.$prefix.'conf` SET `key`=REPLACE(`key`, "VDOT_", "VO2MAX_") WHERE SUBSTR(`key`, 1, 5) = "VDOT_"');

        $this->addSql('DELETE FROM `'.$prefix.'dataset` WHERE `keyid`=18');

        $this->addSql('ALTER TABLE `'.$prefix.'training` DROP `jd_intensity`, CHANGE `vdot` `vo2max` DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL, CHANGE `vdot_by_time` `vo2max_by_time` DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL, CHANGE `vdot_with_elevation` `vo2max_with_elevation` DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL, CHANGE `use_vdot` `use_vo2max` TINYINT(1) UNSIGNED NOT NULL DEFAULT "1", CHANGE `fit_vdot_estimate` `fit_vo2max_estimate` DECIMAL(4,2) UNSIGNED NULL DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

        $this->addSql('UPDATE `'.$prefix.'plugin_conf` SET `config`="show_vdot" WHERE `config`="show_vo2max"');
        $this->addSql('UPDATE `'.$prefix.'plugin_conf` SET `value`="jd" WHERE `config`="model" AND `value`="vo2max"');

        $this->addSql('UPDATE `'.$prefix.'conf` SET `category`="vdot" WHERE `category`="vo2max"');
        $this->addSql('UPDATE `'.$prefix.'conf` SET `key`=REPLACE(`key`, "VO2MAX_", "VDOT_") WHERE SUBSTR(`key`, 1, 7) = "VO2MAX_"');

        $this->addSql('ALTER TABLE `'.$prefix.'training` ADD `jd_intensity` SMALLINT(4) UNSIGNED DEFAULT NULL, CHANGE `vo2max` `vdot` DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL, CHANGE `vo2max_by_time` `vdot_by_time` DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL, CHANGE `vo2max_with_elevation` `vdot_with_elevation` DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL, CHANGE `use_vo2max` `use_vdot` TINYINT(1) UNSIGNED NOT NULL DEFAULT "1", CHANGE `fit_vo2max_estimate` `fit_vdot_estimate` DECIMAL(4,2) UNSIGNED NULL DEFAULT NULL');
    }
}
