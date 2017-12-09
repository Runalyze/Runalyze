<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Import;

use Runalyze\Bundle\CoreBundle\Services\Import\FileImportResult;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

class FileImportResultTest extends \PHPUnit_Framework_TestCase
{
    public function testFailedResult()
    {
        $result = new FileImportResult([], 'foo.bar', 'bar.foo', new \Exception('Test'));

        $this->assertTrue($result->isFailed());
        $this->assertEquals('Test', $result->getException()->getMessage());
    }

    public function testEmptyResult()
    {
        $result = new FileImportResult([], 'foo.bar', 'bar.foo');

        $this->assertFalse($result->isFailed());
        $this->assertEquals(0, $result->getNumberOfActivities());
        $this->assertEmpty($result->getContainer());
        $this->assertEquals('foo.bar', $result->getFileName());
        $this->assertEquals('bar.foo', $result->getOriginalFileName());
    }

    public function testSimpleResult()
    {
        $containerA = new ActivityDataContainer();
        $containerA->Metadata->setDescription('A');

        $containerB = new ActivityDataContainer();
        $containerB->Metadata->setDescription('B');

        $result = new FileImportResult([$containerA, $containerB], 'foo.bar', 'bar.foo');

        $this->assertEquals(2, $result->getNumberOfActivities());
        $this->assertEquals('A', $result->getContainer(0)->Metadata->getDescription());
        $this->assertEquals('B', $result->getContainer(1)->Metadata->getDescription());
        $this->assertCount(2, $result->getContainer());
    }
}
