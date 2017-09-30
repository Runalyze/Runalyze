<?php

namespace Runalyze\Tests\Parser\Activity\Data;

use Runalyze\Parser\Activity\Common\Data\ContinuousData;

class ContinuousDataTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContinuousData */
    protected $Data;

    public function setUp()
    {
        $this->Data = new ContinuousData();
    }

    public function testPropertyAccessByName()
    {
        foreach ($this->Data->getPropertyNamesOfArrays() as $name) {
            $this->assertTrue(is_array($this->Data->$name), 'Can\'t access property "'.$name.'".');
        }
    }

    public function testDistanceCalculationForEmptyData()
    {
        $this->Data->calculateDistancesIfRequired();

        $this->assertEmpty($this->Data->Distance);
    }

    public function testDistanceCalculationDoesNotOverwrite()
    {
        $this->Data->Distance = [0.0, 1.0, 2.0];
        $this->Data->Latitude = [49.77, 49.78, 49.79];
        $this->Data->Longitude = [7.78, 7.76, 7.75];

        $this->Data->calculateDistancesIfRequired();

        $this->assertEquals([0.0, 1.0, 2.0], $this->Data->Distance);
    }

    public function testSimpleDistanceCalculation()
    {
        $this->Data->Latitude = [49.77, 49.78, 49.79];
        $this->Data->Longitude = [7.78, 7.76, 7.75];

        $this->Data->calculateDistancesIfRequired();

        $this->assertEquals(0.0, $this->Data->Distance[0], '', 0.001);
        $this->assertEquals(1.816, $this->Data->Distance[1], '', 0.001);
        $this->assertEquals(3.140, $this->Data->Distance[2], '', 0.001);
    }
}
