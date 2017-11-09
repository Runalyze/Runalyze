<?php

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Data\Weather;
use Runalyze\Profile\Weather\WeatherConditionProfile;

class LegacyDarkskyTest extends \PHPUnit_Framework_TestCase
{
	/** @var \Runalyze\Service\WeatherForecast\Strategy\LegacyDarksky */
	protected $object;

	protected function setUp()
	{
		$this->object = new LegacyDarksky();
	}

	public function testEmptyValues()
	{
		$this->assertNull($this->object->temperature()->value());
		$this->assertEquals(WeatherConditionProfile::UNKNOWN, $this->object->condition()->id());
	}

	public function testLoadForecast()
	{
		$this->object->setFromJSON('{
               "latitude":54.3470453,
               "longitude":10.1125363,
               "timezone":"Europe/Berlin",
               "offset":2,
               "currently":{
                    "time":1474027196,
                  "summary":"Partly Cloudy",
                  "icon":"partly-cloudy-day",
                  "precipIntensity":0,
                  "precipProbability":0,
                  "temperature":73.2,
                  "apparentTemperature":73.2,
                  "dewPoint":57.78,
                  "humidity":0.59,
                  "windSpeed":11.2,
                  "windBearing":109,
                  "cloudCover":0.38,
                  "pressure":1012.66,
                  "ozone":264.7
               }
		}');

		$Temperature = $this->object->temperature();
		$Temperature->toCelsius();

		$this->assertEquals(WeatherConditionProfile::CHANGEABLE, $this->object->condition()->id());
		$this->assertEquals(18.02, $this->object->windSpeed()->value(), '', 0.01);
		$this->assertEquals(109, $this->object->windDegree()->value(),'', 0.1);
		$this->assertEquals(59, $this->object->humidity()->value());
		$this->assertEquals(1013, $this->object->pressure()->value());
		$this->assertEquals(22.77, $Temperature->value(), '', 0.01);
		$this->assertEquals(Weather\Sources::DARKSKY, $this->object->sourceId());
	}
}
