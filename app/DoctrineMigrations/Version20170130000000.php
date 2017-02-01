<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Bernard\Doctrine\MessagesSchema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * bernard mysql schema
 */
class Version20170130000000 extends AbstractMigration implements ContainerAwareInterface
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
        MessagesSchema::create($bernardSchema = new Schema);
        $sql = $bernardSchema->toSql($this->connection->getDatabasePlatform());

        foreach ($sql as $query) {
            $this->connection->exec($query);
        }

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('DROP TABLE bernard_messages');
        $this->addSql('DROP TABLE bernard_queues');

    }
}
