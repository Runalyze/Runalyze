<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * add first dev fit fields to trackdata
 */
class Version20160912090538 extends AbstractMigration implements ContainerAwareInterface
{
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
        $this->addSql('ALTER TABLE `'.$prefix.'trackdata` ADD `smo2_0` longtext AFTER `groundcontact_balance`, 
                                ADD `smo2_1` longtext AFTER `smo2_0`, 
                                ADD `thb_0` longtext AFTER `smo2_1`, 
                                ADD `thb_1` longtext AFTER `thb_0`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'trackdata` DROP `smo2_0`, DROP `smo2_1`, DROP `thb_0`, DROP `thb_1`');
    }
}
