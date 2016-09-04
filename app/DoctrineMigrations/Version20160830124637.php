<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * add birthyear and gender to account
 */
class Version20160830124637 extends AbstractMigration implements ContainerAwareInterface
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
        $this->addSql('ALTER TABLE `'.$prefix.'account` ADD `gender` tinyint(1) unsigned NOT NULL DEFAULT 0, ADD `birthyear` int(4) unsigned DEFAULT NULL');
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        /*$prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'account` DROP `gender`, DROP `birthyear`');*/
        //Not possible, because gender is copied from conf table and deleted afterwards
    }
}
