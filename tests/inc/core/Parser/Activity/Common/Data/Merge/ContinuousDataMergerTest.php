<?php

namespace Runalyze\Tests\Parser\Activity\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\ContinuousData;
use Runalyze\Parser\Activity\Common\Data\Merge\ContinuousDataMerger;

class ContinuousDataMergerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContinuousData */
    protected $FirstData;

    /** @var ContinuousData */
    protected $SecondData;

    public function setUp()
    {
        $this->FirstData = new ContinuousData();
        $this->SecondData = new ContinuousData();
    }

    public function testThatMergeWorksWithEmptyObjects()
    {
        (new ContinuousDataMerger($this->FirstData, $this->SecondData))->merge();
    }

    public function testWithDifferentArraySizes()
    {
        $this->FirstData->Time = [1, 2, 3];
        $this->SecondData->Distance = [1, 2, 3, 4];

        $this->setExpectedException(\RuntimeException::class);

        (new ContinuousDataMerger($this->FirstData, $this->SecondData))->merge(true);
    }

    public function testWithEmptySecondObject()
    {
        $this->FirstData->Time = [1, 2, 3];

        (new ContinuousDataMerger($this->FirstData, $this->SecondData))->merge(true);

        $this->assertEquals([1, 2, 3], $this->FirstData->Time);
    }

    public function testWithEmptyFirstObject()
    {
        $this->SecondData->Time = [1, 2, 3];

        (new ContinuousDataMerger($this->FirstData, $this->SecondData))->merge(true);

        $this->assertEquals([1, 2, 3], $this->FirstData->Time);
        $this->assertNull($this->FirstData->IsAltitudeDataBarometric);
    }

    public function testThatBarometricAltitudeFromFirstObjectIsPreferred()
    {
        $this->FirstData->Altitude = [120, 125, 120];
        $this->FirstData->IsAltitudeDataBarometric = true;
        $this->SecondData->Altitude = [121, 124, 122];

        (new ContinuousDataMerger($this->FirstData, $this->SecondData))->merge(true);

        $this->assertEquals([120, 125, 120], $this->FirstData->Altitude);
        $this->assertTrue($this->FirstData->IsAltitudeDataBarometric);
    }

    public function testThatBarometricAltitudeFromSecondObjectIsPreferred()
    {
        $this->FirstData->Altitude = [120, 125, 120];
        $this->FirstData->IsAltitudeDataBarometric = false;
        $this->SecondData->Altitude = [121, 124, 122];
        $this->SecondData->IsAltitudeDataBarometric = true;

        (new ContinuousDataMerger($this->FirstData, $this->SecondData))->merge(true);

        $this->assertEquals([121, 124, 122], $this->FirstData->Altitude);
        $this->assertTrue($this->FirstData->IsAltitudeDataBarometric);
    }
}
