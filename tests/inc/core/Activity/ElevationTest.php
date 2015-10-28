<?php

namespace Runalyze\Activity;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\DistanceUnitSystem;

class ElevationTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$this->assertEquals('100', (new Elevation(100, new DistanceUnitSystem(DistanceUnitSystem::METRIC)))->string(false));
		$this->assertEquals('328', (new Elevation(100, new DistanceUnitSystem(DistanceUnitSystem::IMPERIAL)))->string(false));
	}

	public function testSettingInPreferredUnit()
	{
		$this->assertEquals(100, (new Elevation(0, new DistanceUnitSystem(DistanceUnitSystem::METRIC)))->setInPreferredUnit(100)->meter());
		$this->assertEquals(30, (new Elevation(0, new DistanceUnitSystem(DistanceUnitSystem::IMPERIAL)))->setInPreferredUnit(100)->meter());
	}

	public function testFromMeter()
	{
		$Elevation = new Elevation();
		$Elevation->set(100);

		$this->assertEquals(100, $Elevation->meter());
		$this->assertEquals(328, $Elevation->feet());
	}

	public function testFromFeet()
	{
		$Elevation = new Elevation();
		$Elevation->setFeet(100);

		$this->assertEquals(30, $Elevation->meter());
		$this->assertEquals(100, $Elevation->feet());
	}

	public function testStaticMethod()
	{
		Configuration::General()->distanceUnitSystem()->set(DistanceUnitSystem::METRIC);

		$this->assertEquals('123', Elevation::format(123, false));
	}
}
