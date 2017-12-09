<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Import;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Runalyze\Bundle\CoreBundle\Services\Import\FileImporter;
use Runalyze\Parser\Activity\Converter\FitConverter;
use Runalyze\Parser\Activity\Converter\TtbinConverter;

class FileImporterTest extends \PHPUnit_Framework_TestCase
{
    /** @var FileImporter */
    protected $Importer;

    protected function setUp()
    {
        $this->Importer = new FileImporter(
            new FitConverter('', ''),
            new TtbinConverter('')
        );
    }

    /**
     * @return TestHandler
     */
    protected function setLoggerToImporter()
    {
        $handler = new TestHandler();

        $this->Importer->setLogger(new Logger('test', [$handler]));

        return $handler;
    }

    public function testSupportedFileExtensions()
    {
        $this->assertContains('zip', $this->Importer->getSupportedFileExtensions());
    }

    public function testThatSingleFailedImportIsLogged()
    {
        $handler = $this->setLoggerToImporter();
        $results = $this->Importer->importSingleFile('idontexist.foo');

        $this->assertCount(1, $results);
        $this->assertEquals(0, $results->getTotalNumberOfActivities());
        $this->assertTrue($results[0]->isFailed());

        $this->assertTrue($handler->hasErrorThatContains('idontexist.foo'));
    }

    public function testMultipleNonExistingFiles()
    {
        $handler = $this->setLoggerToImporter();
        $results = $this->Importer->importFiles([
            'none.fit',
            'foobar.zip',
            'test.csv'
        ]);

        $this->assertCount(3, $results);
        $this->assertEquals(0, $results->getTotalNumberOfActivities());
        $this->assertTrue($results[0]->isFailed());
        $this->assertTrue($results[1]->isFailed());
        $this->assertTrue($results[2]->isFailed());

        $this->assertTrue($handler->hasErrorThatContains('none.fit'));
        $this->assertTrue($handler->hasErrorThatContains('foobar.zip'));
        $this->assertTrue($handler->hasErrorThatContains('test.csv'));
    }

    public function testMergingHrmAndGpx()
    {
        $results = $this->Importer->importFiles([
            TESTS_ROOT.'/testfiles/hrm/12010401.gpx',
            TESTS_ROOT.'/testfiles/hrm/12010401.hrm'
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->getTotalNumberOfActivities());

        $container = $results[0]->getContainer(0);

        $this->assertNotEmpty($container->ContinuousData->Latitude);
        $this->assertNotEmpty($container->ContinuousData->Longitude);
        $this->assertNotEmpty($container->ContinuousData->HeartRate);
        $this->assertNotEmpty($container->ContinuousData->Time);
    }
}
