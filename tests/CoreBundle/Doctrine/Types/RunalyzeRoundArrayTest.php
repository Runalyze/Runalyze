<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Runalyze\Bundle\CoreBundle\Doctrine\Types\RunalyzeRoundArray;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;

class RunalyzeRoundArrayTest extends \PHPUnit_Framework_TestCase
{
    /** @var RunalyzeRoundArray */
    protected $Type;

    /** @var AbstractPlatform */
    protected $PlatformMock;

    public function setUp()
    {
        $this->Type = RunalyzeRoundArray::getType(RunalyzeRoundArray::RUNALYZE_ROUND_ARRAY);
        $this->PlatformMock = $this->getMockForAbstractClass(AbstractPlatform::class);
    }

    public function testEmptyData()
    {
        $this->assertNull($this->Type->convertToDatabaseValue(null, $this->PlatformMock));
        $this->assertNull($this->Type->convertToDatabaseValue('', $this->PlatformMock));
        $this->assertNull($this->Type->convertToDatabaseValue(new RoundCollection(), $this->PlatformMock));

        $nullResult = $this->Type->convertToPHPValue(null, $this->PlatformMock);
        $this->assertInstanceOf(RoundCollection::class, $nullResult);
        $this->assertTrue($nullResult->isEmpty());

        $emptyResult = $this->Type->convertToPHPValue('', $this->PlatformMock);
        $this->assertInstanceOf(RoundCollection::class, $emptyResult);
        $this->assertTrue($emptyResult->isEmpty());
    }

    public function testSimpleData()
    {
        $collection = new RoundCollection([
            new Round(1.0, 311, false),
            new Round(1.0, 189, true),
            new Round(1.0, 267, false)
        ]);

        $newCollection = $this->Type->convertToPHPValue($this->Type->convertToDatabaseValue($collection, $this->PlatformMock), $this->PlatformMock);

        $this->assertEquals(3, $newCollection->count());
        $this->assertEquals(1.0, $newCollection[0]->getDistance());
        $this->assertEquals(311, $newCollection[0]->getDuration());
        $this->assertFalse($newCollection[0]->isActive());
        $this->assertEquals(1.0, $newCollection[1]->getDistance());
        $this->assertEquals(189, $newCollection[1]->getDuration());
        $this->assertTrue($newCollection[1]->isActive());
        $this->assertEquals(1.0, $newCollection[2]->getDistance());
        $this->assertEquals(267, $newCollection[2]->getDuration());
        $this->assertFalse($newCollection[2]->isActive());
    }

    public function testLegacyExample()
    {
        $collection = $this->Type->convertToPHPValue(
            'R1.200|5:20-0.400|1:20-1.000|3:17',
            $this->PlatformMock
        );

        $this->assertEquals(3, $collection->count());
        $this->assertEquals([1.2, 320, false], [$collection[0]->getDistance(), $collection[0]->getDuration(), $collection[0]->isActive()]);
        $this->assertEquals([0.4, 80, true], [$collection[1]->getDistance(), $collection[1]->getDuration(), $collection[1]->isActive()]);
        $this->assertEquals([1.0, 197, true], [$collection[2]->getDistance(), $collection[2]->getDuration(), $collection[2]->isActive()]);
    }
}
