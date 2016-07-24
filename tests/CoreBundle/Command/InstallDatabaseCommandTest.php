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

    protected function setUp()
    {
        $this->Kernel = $this->createKernel();
        $this->Kernel->boot();

        $this->Connection = $this->Kernel->getContainer()->get('doctrine')->getConnection();

        $this->dropAllTables();
    }

    protected function tearDown()
    {
        $this->dropAllTables();
    }

    protected function dropAllTables()
    {
        $prefix = $this->Kernel->getContainer()->getParameter('database_prefix');
        $stmt = $this->Connection->executeQuery('SHOW TABLES LIKE "'.$prefix.'%"');
        $stmt->setFetchMode(PDOConnection::FETCH_COLUMN, 0);
        $tables = $stmt->fetchAll();

        if (!empty($tables)) {
            $this->Connection->exec('SET foreign_key_checks = 0');
            $this->Connection->executeQuery('DROP TABLE `'.implode($tables, '`, `').'`');
            $this->Connection->exec('SET foreign_key_checks = 1');
        }
    }

    public function testExecute()
    {
        $application = new Application($this->Kernel);
        $application->add(new InstallDatabaseCommand());

        $command = $application->find('runalyze:install:database');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $prefix = $this->Kernel->getContainer()->getParameter('database_prefix');
        $this->assertEquals(21, $this->Connection->query(
            'SHOW TABLES LIKE "'.$prefix.'%"'
        )->rowCount());

        $this->assertRegExp('/Database has been successfully initialized./', $commandTester->getDisplay());
    }
}
