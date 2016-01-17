<?php

namespace Runalyze\Data\Weather;

class OpenweathermapTest extends \PHPUnit_Framework_TestCase {

	/** @var \Runalyze\Data\Weather\Openweathermap */
	protected $object;

	protected function setUp() {
		$this->object = new Openweathermap;
	}

	public function testEmptyValues() {
		$this->assertNull($this->object->temperature()->value());
		$this->assertEquals(Condition::UNKNOWN, $this->object->condition()->id());
	}

	public function testLoadForecast() {
		$this->object->setFromJSON('
			{
				"coord":{"lon":7.75,"lat":49.45},
				"sys":{"message":0.0221,"country":"DE","sunrise":1401334144,"sunset":1401391431},
				"weather":[{"id":803,"main":"Clouds","description":"broken clouds","icon":"04d"}],
				"base":"cmc stations",
				"main":{"temp":290,"pressure":1013.456,"temp_min":16.11,"temp_max":18.2,"humidity":59.24},
				"wind":{"speed":1.76,"deg":313.5},
				"clouds":{"all":76},
				"dt":1401371226,
				"id":2894003,
				"name":"Kaiserslautern",
				"cod":200
			}');

		$Temperature = $this->object->temperature();
		$Temperature->toCelsius();

		$this->assertEquals(Condition::CLOUDY, $this->object->condition()->id());
		$this->assertEquals(2.83, $this->object->windSpeed()->value(), '', 0.01);
		$this->assertEquals(314, $this->object->windDegree()->value());
		$this->assertEquals(59, $this->object->humidity()->value());
		$this->assertEquals(1013, $this->object->pressure()->value());
		$this->assertEquals(16.85, $Temperature->value());
	}
}
