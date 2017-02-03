<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20170202220915 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('UPDATE `'.$prefix.'route` SET `geohashes` = NULL WHERE `geohashes`= ""');
        $this->addSql('UPDATE `'.$prefix.'route` SET `elevations_original` = NULL WHERE `elevations_original`= ""');
        $this->addSql('UPDATE `'.$prefix.'route` SET `elevations_corrected` = NULL WHERE `elevations_corrected`= ""');

        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `time` = NULL WHERE `time`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `distance` = NULL WHERE `distance`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `heartrate` = NULL WHERE `heartrate`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `cadence` = NULL WHERE `cadence`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `power` = NULL WHERE `power`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `temperature` = NULL WHERE `temperature`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `groundcontact` = NULL WHERE `groundcontact`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `vertical_oscillation` = NULL WHERE `vertical_oscillation`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `groundcontact_balance` = NULL WHERE `groundcontact_balance`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `smo2_0` = NULL WHERE `smo2_0`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `smo2_1` = NULL WHERE `smo2_1`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `thb_0` = NULL WHERE `thb_0`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `thb_1` = NULL WHERE `thb_1`= ""');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `pauses` = NULL WHERE `pauses`= ""');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
