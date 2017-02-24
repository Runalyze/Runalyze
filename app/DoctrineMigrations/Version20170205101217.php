<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20170205101217 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('DELETE FROM `'.$prefix.'plugin_conf` WHERE `config`="show_trainingpaces" OR `config`="show_jd_intensity" OR `config`="model-jd" OR `config`="model-cpp" OR `config`="model-steffny" OR `config`="model-cameron"');
        $this->addSql('UPDATE `'.$prefix.'plugin_conf` SET `config`="show_vo2max" WHERE `config`="show_vdot"');
        $this->addSql('UPDATE `'.$prefix.'plugin_conf` SET `config`="vo2max" WHERE `config`="model" AND `value`="jd"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
