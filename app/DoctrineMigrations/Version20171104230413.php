<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20171104230413 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

        $this->addSql('ALTER TABLE `'.$prefix.'trackdata` ADD `lock` tinyint(1) unsigned NOT NULL DEFAULT 0');
        $this->addSql('UPDATE `'.$prefix.'trackdata` SET `lock` = 1 WHERE `distance` IS NOT NULL OR `cadence` IS NOT NULL OR `heartrate` IS NOT NULL');
        $this->addSql('UPDATE `'.$prefix.'route` SET `lock` = 1 WHERE `elevations_corrected` IS NOT NULL');
    }

    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'trackdata` DROP `lock`');
    }
}
