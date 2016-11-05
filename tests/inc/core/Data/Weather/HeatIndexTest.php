<?php

use Runalyze\Activity\Temperature;
use Runalyze\Data\Weather\HeatIndex;
use Runalyze\Data\Weather\Humidity;
use Runalyze\Parameter\Application\TemperatureUnit;


class HeatIndexTest extends \PHPUnit_Framework_TestCase
{
	public function testUnknownHeatIndex()
	{
		$obj = new HeatIndex();
		
		$this->assertTrue($obj->isUnknown());
	}
	
	public function testManualSetting()
	{
		$obj = new HeatIndex();
		$obj->set(42);
		
		$this->assertEquals(42, $obj->value());
	}
	
	public function testTryingToSetInvalidValue()
	{
		$obj = new HeatIndex();
		
		$this->setExpectedException(\InvalidArgumentException::class);
		
		$obj->set('foobar');
	}
	
	public function testSomeValues()
	{
		// [temperature, humidity, heatIndex]
		$valuesToTest = [
			[60, 0, 56],
			[60, 100, 60],
			[80, 40, 80],
			[80, 100, 87],
			[88, 65, 98],
			[98, 50, 113],
			[96, 75, 132]
		];

		$temp = new Temperature(null, new TemperatureUnit(TemperatureUnit::FAHRENHEIT));
		$humidity = new Humidity();
		
		foreach ($valuesToTest as $i => $dataToTest) {
			$temp->setFahrenheit($dataToTest[0]);
			$humidity->set($dataToTest[1]);
			
			$this->assertEquals(
				$dataToTest[2], (new HeatIndex($temp, $humidity))->value(),
				sprintf('Heat index does not match for $valuesToTest[%u]', $i), 0.5
			);
		}
	}
}
