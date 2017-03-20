<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20170320194525 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('CREATE TABLE IF NOT EXISTS `'.$prefix.'notification` (
                          `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
                          `template`  tinyint unsigned,
                          `createdAt` DATETIME NOT NULL,
                          `expirationAt` DATETIME DEFAULT NULL,
                          `data` TINYTEXT NOT NULL,
                          `account_id` INT UNSIGNED NOT NULL,
                          INDEX IDX_F99B51889B6B5FBA (account_id),
                          PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
                        ');
        $this->addSql('ALTER TABLE runalyze_notification ADD CONSTRAINT FK_F99B51889B6B5FBA FOREIGN KEY (account_id) REFERENCES runalyze_account (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

        $this->addSql('DROP TABLE `'.$prefix.'notification`');
    }
}
