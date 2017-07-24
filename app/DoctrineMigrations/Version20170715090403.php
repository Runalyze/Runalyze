<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20170715090403 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('UPDATE `'.$prefix.'sport` SET main_equipmenttypeid=NULL WHERE main_equipmenttypeid = 0');
        $this->addSql('UPDATE `'.$prefix.'sport` s LEFT JOIN `'.$prefix.'equipment_type` t ON t.id=s.main_equipmenttypeid SET s.main_equipmenttypeid=NULL WHERE s.main_equipmenttypeid IS NOT NULL AND t.id IS NULL');
        $this->addSql('UPDATE `'.$prefix.'sport` s LEFT JOIN `'.$prefix.'type` t ON t.id=s.default_typeid SET s.default_typeid=NULL WHERE s.default_typeid IS NOT NULL AND t.id IS NULL');
        $this->addSql('ALTER TABLE `'.$prefix.'sport` ADD FOREIGN KEY (main_equipmenttypeid) REFERENCES `'.$prefix.'equipment_type` (id) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->addSql('ALTER TABLE `'.$prefix.'sport` ADD FOREIGN KEY (default_typeid) REFERENCES `'.$prefix.'type` (id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
