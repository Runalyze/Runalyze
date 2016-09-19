<?php

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Data\Weather;

class ForecastioTest extends \PHPUnit_Framework_TestCase
{
	/** @var \Runalyze\Service\WeatherForecast\Strategy\Forecastio */
	protected $object;

	protected function setUp()
	{
		$this->object = new Forecastio();
	}

	public function testEmptyValues()
	{
		$this->assertNull($this->object->temperature()->value());
		$this->assertEquals(Weather\Condition::UNKNOWN, $this->object->condition()->id());
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

		$this->assertEquals(Weather\Condition::CHANGEABLE, $this->object->condition()->id());
		$this->assertEquals(40.32, $this->object->windSpeed()->value(), '', 0.01);
		$this->assertEquals(109, $this->object->windDegree()->value(),'', 0.1);
		$this->assertEquals(59, $this->object->humidity()->value());
		$this->assertEquals(1013, $this->object->pressure()->value());
		$this->assertEquals(22.77, $Temperature->value(), '', 0.01);
		$this->assertEquals(Weather\Sources::FORECASTIO, $this->object->sourceId());
	}
}
