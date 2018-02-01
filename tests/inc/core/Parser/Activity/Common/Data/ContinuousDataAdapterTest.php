<?php

namespace Runalyze\Tests\Parser\Activity\Data;

use Runalyze\Parser\Activity\Common\Data\ContinuousData;
use Runalyze\Parser\Activity\Common\Data\ContinuousDataAdapter;

class ContinuousDataAdapterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContinuousData */
    protected $Data;

    /** @var ContinuousDataAdapter */
    protected $Adapter;

    public function setUp()
    {
        $this->Data = new ContinuousData();
        $this->Adapter = new ContinuousDataAdapter($this->Data);
    }

    public function testDistanceCalculationForEmptyData()
    {
        $this->Adapter->calculateDistancesIfRequired();

        $this->assertEmpty($this->Data->Distance);
    }

    public function testDistanceCalculationDoesNotOverwrite()
    {
        $this->Data->Distance = [0.0, 1.0, 2.0];
        $this->Data->Latitude = [49.77, 49.78, 49.79];
        $this->Data->Longitude = [7.78, 7.76, 7.75];

        $this->Adapter->calculateDistancesIfRequired();

        $this->assertEquals([0.0, 1.0, 2.0], $this->Data->Distance);
    }

    public function testSimpleDistanceCalculation()
    {
        $this->Data->Latitude = [49.77, 49.78, 49.79];
        $this->Data->Longitude = [7.78, 7.76, 7.75];

        $this->Adapter->calculateDistancesIfRequired();

        $this->assertEquals(0.0, $this->Data->Distance[0], '', 0.001);
        $this->assertEquals(1.816, $this->Data->Distance[1], '', 0.001);
        $this->assertEquals(3.140, $this->Data->Distance[2], '', 0.001);
    }

    public function testClearingEmptyArraysIfAllArraysAreAlreadyEmpty()
    {
        $this->Adapter->clearEmptyArrays();
    }

    public function testClearingEmptyArrays()
    {
        $this->Data->Distance = [0.0, 1.0, 2.0, 3.0];
        $this->Data->HeartRate = [null, 120, 140, null];
        $this->Data->Altitude = [null, null, null, null];

        $this->Adapter->clearEmptyArrays();

        $this->assertNotEmpty($this->Data->Distance);
        $this->assertNotEmpty($this->Data->HeartRate);
        $this->assertEmpty($this->Data->Altitude);
    }

    public function testFilteringUnwantedZeros()
    {
        $this->Data->HeartRate = [0, 120, 140, 0];

        $this->Adapter->filterUnwantedZeros();

        $this->assertEquals([null, 120, 140, null], $this->Data->HeartRate);
    }

    public function testClearingEmptyArraysAfterFilteringUnwantedZeros()
    {
        $this->Data->HeartRate = [0, 0, 0, 0];

        $this->Adapter->filterUnwantedZeros();
        $this->Adapter->clearEmptyArrays();

        $this->assertEmpty($this->Data->HeartRate);
    }
}
