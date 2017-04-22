<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
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

        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::KILOMETER_PER_HOUR.' WHERE `speed`="km/h"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::MILES_PER_HOUR.' WHERE `speed`="mph"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::SECONDS_PER_KILOMETER.' WHERE `speed`="min/km"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::SECONDS_PER_MILE.' WHERE `speed`="min/mi"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::METER_PER_SECOND.' WHERE `speed`="m/s"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::SECONDS_PER_100M.' WHERE `speed`="min/100m"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::SECONDS_PER_100Y.' WHERE `speed`="min/100y"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::SECONDS_PER_500M.' WHERE `speed`="min/500m"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::SECONDS_PER_500Y.' WHERE `speed`="min/500y"');

        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`='.PaceEnum::SECONDS_PER_KILOMETER.' WHERE `speed` NOT IN ("0","1","2","3","4","5","6","7","8")');
        $this->addSql('ALTER TABLE `'.$prefix.'sport` MODIFY `speed` tinyint unsigned NOT NULL DEFAULT 6');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('ALTER TABLE `'.$prefix.'sport` MODIFY `speed` varchar(10) NOT NULL DEFAULT "min/km"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="km/h" WHERE `speed`="'.PaceEnum::KILOMETER_PER_HOUR.'"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="mph" WHERE `speed`="'.PaceEnum::MILES_PER_HOUR.'"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/km" WHERE `speed`="'.PaceEnum::SECONDS_PER_KILOMETER.'"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/mi" WHERE `speed`="'.PaceEnum::SECONDS_PER_MILE.'"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="m/s" WHERE `speed`="'.PaceEnum::METER_PER_SECOND.'"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/100m" WHERE `speed`="'.PaceEnum::SECONDS_PER_100M.'"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/100y" WHERE `speed`="'.PaceEnum::SECONDS_PER_100Y.'"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/500m" WHERE `speed`="'.PaceEnum::SECONDS_PER_500M.'"');
        $this->addSql('UPDATE `'.$prefix.'sport` SET `speed`="min/500y" WHERE `speed`="'.PaceEnum::SECONDS_PER_500Y.'"');
    }
}
