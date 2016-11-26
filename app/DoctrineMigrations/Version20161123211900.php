<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Set `training.routeid` nullable
 */
class Version20161123211900 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var string */
    private $Prefix;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->Prefix = $container->getParameter('database_prefix');
    }

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `'.$this->Prefix.'training` MODIFY `routeid` int(10) unsigned DEFAULT NULL');
        $this->addSql('UPDATE `'.$this->Prefix.'training` SET `routeid` = NULL WHERE `routeid`= 0');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}
