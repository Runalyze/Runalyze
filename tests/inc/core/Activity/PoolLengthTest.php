<?php

namespace Runalyze\Activity;

use Runalyze\Parameter\Application\DistanceUnitSystem;

class PoolLengthTest extends \PHPUnit_Framework_TestCase
{
	public function testStaticFunction()
	{
		$this->assertEquals('25&nbsp;'.DistanceUnitSystem::METER, PoolLength::format(2500, true));
		$this->assertEquals('25', PoolLength::format(2500, false));
	}

	public function testInMeter()
	{
		$PoolLength = new PoolLength();
		$PoolLength->setMeter(50);

		$this->assertEquals(50, $PoolLength->meter());
		$this->assertEquals('50&nbsp;'.DistanceUnitSystem::METER, $PoolLength->stringMeter());
	}

	public function testInCentimeter()
	{
		$PoolLength = new PoolLength();
		$PoolLength->set(5000);

		$this->assertEquals(5000, $PoolLength->cm());
		$this->assertEquals('5000&nbsp;'.DistanceUnitSystem::CM, $PoolLength->stringCM());
	}

	public function testInYards()
	{
		$PoolLength = new PoolLength();
		$PoolLength->setYards(27.34);

		$this->assertEquals(27.34, $PoolLength->yards());
		$this->assertEquals('27.34&nbsp;'.DistanceUnitSystem::YARDS, $PoolLength->stringYards());
	}

	public function testSettingInPreferredUnit()
	{
		$this->assertEquals(25, (new PoolLength(0, new DistanceUnitSystem(DistanceUnitSystem::METRIC)))->setInPreferredUnit(25)->meter());
		$this->assertEquals(54.68, (new PoolLength(0, new DistanceUnitSystem(DistanceUnitSystem::IMPERIAL)))->setInPreferredUnit(54.68)->yards());
	}
}
