<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * rectify sport column main_equipmenttypeid
 */
class Version20160918165655 extends AbstractMigration implements ContainerAwareInterface
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
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'sport` CHANGE `main_equipmenttypeid` `main_equipmenttypeid` int(10) unsigned DEFAULT NULL, CHANGE `accountid` `accountid` int(11) unsigned NOT NULL');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `main_equipmenttypeid` = NULL WHERE `main_equipmenttypeid`= 0');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}
