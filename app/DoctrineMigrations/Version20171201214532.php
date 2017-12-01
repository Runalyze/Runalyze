<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\ClimbScoreCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\FlatOrHillyAnalyzer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20171201214532 extends AbstractMigration implements ContainerAwareInterface
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
        $this->addSql('ALTER TABLE `'.$prefix.'dataset` ADD `privacy` tinyint(3) unsigned NOT NULL DEFAULT 0 AFTER `position`');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'dataset` DROP COLUMN `privacy');
    }
}
