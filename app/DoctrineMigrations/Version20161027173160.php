<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * database cleanup
 */
class Version20161027173160 extends AbstractMigration implements ContainerAwareInterface
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
        //Table: conf
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('DELETE FROM `'.$prefix.'equipment` WHERE `typeid` = 0');
        $this->addSql('ALTER TABLE `'.$prefix.'equipment`
                  MODIFY `typeid` int(10) unsigned NOT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}
