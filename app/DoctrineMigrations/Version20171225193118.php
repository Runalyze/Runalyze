<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20171225193118 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('ALTER TABLE `'.$prefix.'training` 
                                ADD avg_impact_gs_left DOUBLE PRECISION DEFAULT NULL, 
                                ADD avg_impact_gs_right DOUBLE PRECISION DEFAULT NULL, 
                                ADD avg_braking_gs_left DOUBLE PRECISION DEFAULT NULL, 
                                ADD avg_braking_gs_right DOUBLE PRECISION DEFAULT NULL, 
                                ADD avg_footstrike_type_left TINYINT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:tinyint)\', 
                                ADD avg_footstrike_type_right TINYINT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:tinyint)\', 
                                ADD avg_pronation_excursion_left DOUBLE PRECISION DEFAULT NULL, 
                                ADD avg_pronation_excursion_right DOUBLE PRECISION DEFAULT NULL;
                                ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'training` 
                                DROP avg_impact_gs_left, 
                                DROP avg_impact_gs_right, 
                                DROP avg_braking_gs_left, 
                                DROP avg_braking_gs_right, 
                                DROP avg_footstrike_type_left, 
                                DROP avg_footstrike_type_right 
                                DROP avg_pronation_excursion_left, 
                                DROP avg_pronation_excursion_right;
                                ');
    }
}
