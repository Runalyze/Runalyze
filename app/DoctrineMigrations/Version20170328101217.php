<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20170328101217 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=0 WHERE `speed`="km/h"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=1 WHERE `speed`="mph"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=2 WHERE `speed`="min/km"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=3 WHERE `speed`="min/mi"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=4 WHERE `speed`="m/s"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=5 WHERE `speed`="min/100m"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=6 WHERE `speed`="min/100y"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=7 WHERE `speed`="min/500m"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`=8 WHERE `speed`="min/500y"');


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="km/h" WHERE `speed`=0');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="mph" WHERE `speed`=1');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/km" WHERE `speed`=2');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/mi" WHERE `speed`=3');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="m/s" WHERE `speed`=4');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/100m" WHERE `speed`=5');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/100y" WHERE `speed`=6');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/500m" WHERE `speed`=7');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/500y" WHERE `speed`=8');

    }
}
