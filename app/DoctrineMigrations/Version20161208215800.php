<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20161208215800 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface|null */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

        $this->addSql('UPDATE `'.$prefix.'training` SET `typeid` = NULL WHERE `typeid`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `created` = NULL WHERE `created`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `edited` = NULL WHERE `edited`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `distance` = NULL WHERE `distance` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `elapsed_time` = NULL WHERE `elapsed_time` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `elevation` = NULL WHERE `elevation` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `kcal` = NULL WHERE `kcal` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `pulse_avg` = NULL WHERE `pulse_avg` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `pulse_max` = NULL WHERE `pulse_max` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vdot` = NULL WHERE `vdot`= \'0.00\'');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vdot_by_time` = NULL WHERE `vdot_by_time`= \'0.00\'');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vdot_with_elevation` = NULL WHERE `vdot_with_elevation`= \'0.00\'');
        $this->addSql('UPDATE `'.$prefix.'training` SET `fit_vdot_estimate` = NULL WHERE `fit_vdot_estimate`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'training` SET `fit_recovery_time` = NULL WHERE `fit_recovery_time`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `fit_hrv_analysis` = NULL WHERE `fit_hrv_analysis`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `fit_performance_condition` = NULL WHERE `fit_performance_condition` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `jd_intensity` = NULL WHERE `jd_intensity`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `trimp` = NULL WHERE `trimp` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `cadence` = NULL WHERE `cadence` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `power` = NULL WHERE `power` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `total_strokes` = NULL WHERE `total_strokes`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `swolf` = NULL WHERE `swolf`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `stride_length` = NULL WHERE `stride_length`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `groundcontact` = NULL WHERE `groundcontact`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `groundcontact_balance` = NULL WHERE `groundcontact_balance`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vertical_oscillation` = NULL WHERE `vertical_oscillation`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vertical_ratio` = NULL WHERE `vertical_ratio`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `routeid` = NULL WHERE `routeid` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `activity_id` = NULL WHERE `activity_id` = 0');
    }

    public function down(Schema $schema)
    {
    }
}
