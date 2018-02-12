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

    public function testTotalDurationAndDistance()
    {
        $this->Data->Time = [1, 2, 3, 4, 5, 6];
        $this->Data->Distance = [0.005, 0.011, 0.016, 0.020, 0.024, 0.026];

        $this->assertEquals(6, $this->Data->getTotalDuration());
        $this->assertEquals(0.026, $this->Data->getTotalDistance());
    }

    public function testTotalDurationAndDistanceIfNotPresent()
    {
        $this->Data->HeartRate = [126, 128, 129, 130, 130, 133];

        $this->assertNull($this->Data->getTotalDuration());
        $this->assertNull($this->Data->getTotalDistance());
    }

    public function testTotalDurationAndDistanceForEmptyArrays()
    {
        $this->Data->Time = [0, 0, 0, 0, 0, 0];
        $this->Data->Distance = [0.0, 0.0, 0.0, 0.0, 0.0, 0.0];
        $this->Data->HeartRate = [126, 128, 129, 130, 130, 133];

        $this->assertNull($this->Data->getTotalDuration());
        $this->assertNull($this->Data->getTotalDistance());
    }
}
