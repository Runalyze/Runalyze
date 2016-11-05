<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Command;

use Doctrine\DBAL\Driver\PDOConnection;
use Runalyze\Bundle\CoreBundle\Command\InstallDatabaseCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InstallDatabaseCommandTest extends KernelTestCase
{
    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    protected $Kernel;

    /** @var \Doctrine\DBAL\Connection */
    protected $Connection;

    /** @var string */
    protected $DatabasePrefix;

    protected function setUp()
    {
        static::bootKernel();

        $this->Connection = static::$kernel->getContainer()->get('doctrine')->getConnection();
        $this->DatabasePrefix = static::$kernel->getContainer()->getParameter('database_prefix');

        if (null === $this->Connection) {
            $this->markTestSkipped('No doctrine connection available, maybe cache needs to be cleared.');
        }

        $this->dropAllTables();
    }

    protected function tearDown()
    {
        $this->dropAllTables();

        parent::tearDown();
    }

    protected function dropAllTables()
    {
        if (null !== $this->Connection) {
            $stmt = $this->Connection->executeQuery('SHOW TABLES LIKE "'.$this->DatabasePrefix.'%"');
            $stmt->setFetchMode(PDOConnection::FETCH_COLUMN, 0);
            $tables = $stmt->fetchAll();

            if (!empty($tables)) {
                $this->Connection->exec('SET foreign_key_checks = 0');
                $this->Connection->executeQuery('DROP TABLE `'.implode($tables, '`, `').'`');
                $this->Connection->exec('SET foreign_key_checks = 1');
            }
        }
    }

    public function testExecute()
    {
        $application = new Application(static::$kernel);
        $application->add(new InstallDatabaseCommand());

        $command = $application->find('runalyze:install:database');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(23, $this->Connection->query(
            'SHOW TABLES LIKE "'.$this->DatabasePrefix.'%"'
        )->rowCount());

        $this->assertRegExp('/Database has been successfully initialized./', $commandTester->getDisplay());
    }
}
