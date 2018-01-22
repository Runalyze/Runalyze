<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * add runscribe data to trackdata
 */
class Version20180117090538 extends AbstractMigration implements ContainerAwareInterface
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
        $this->addSql('ALTER TABLE `'.$prefix.'trackdata` ADD `impact_gs_left` longtext AFTER `thb_0`,
                                ADD `impact_gs_right` longtext AFTER `impact_gs_left`,
                                ADD `braking_gs_left` longtext AFTER `impact_gs_right`,
                                ADD `braking_gs_right` longtext AFTER `braking_gs_left`,
                                ADD `footstrike_type_left` longtext AFTER `braking_gs_right`,
                                ADD `footstrike_type_right` longtext AFTER `footstrike_type_left`,
                                ADD `pronation_excursion_left` longtext AFTER `footstrike_type_right`,
                                ADD `pronation_excursion_right` longtext AFTER `pronation_excursion_left`
                                ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'trackdata`
                                DROP `impact_gs_left`,
                                DROP `impact_gs_right`,
                                DROP `braking_gs_left`,
                                DROP `braking_gs_right`,
                                DROP `footstrike_type_left`,
                                DROP `footstrike_type_right`,
                                DROP `pronation_excursion_left`,
                                DROP `pronation_excursion_right`
                                ');
    }
}
