<?php

namespace Runalyze\Data\Weather\Strategy;

use Runalyze\Data\Weather;
use Runalyze\Model;

class DBWeatherCacheTest extends \PHPUnit_Framework_TestCase
{
	/** @var \Runalyze\Data\Weather\Strategy\DBWeatherCache */
	protected $object;

	/** @var \PDO */
	protected $PDO;

	protected function setUp()
	{
		$this->object = new DBWeatherCache;
		$this->PDO = \DB::getInstance();
		$this->PDO->exec('DELETE FROM `'.PREFIX.'weathercache`');
	}

	protected function tearDown()
	{
		$this->PDO->exec('DELETE FROM `'.PREFIX.'weathercache`');
	}

	protected function insertCacheData(array $cacheData)
	{
		$WeatherCacheEntity = new Model\WeatherCache\Entity($cacheData);

		$Inserter = new Model\WeatherCache\Inserter($this->PDO, $WeatherCacheEntity);
		$Inserter->insert();
	}

	public function testEmptyValues()
	{
		$this->assertNull($this->object->temperature()->value());
		$this->assertEquals(Weather\Condition::UNKNOWN, $this->object->condition()->id());
	}

	public function testLoadForecastWithEmptyCache()
	{
		$Location = new Weather\Location();
		$Location->setTimestamp(1462289510);
		$Location->setGeohash('u1xjn3n74zxv');

		$this->object->loadForecast($Location);

		$this->assertFalse($this->object->wasSuccessfull());
	}

	public function testLoadForecast()
	{
		$this->insertCacheData([
			Model\WeatherCache\Entity::TIME => '1462289510',
			Model\WeatherCache\Entity::GEOHASH => 'u1xjn',
			Model\WeatherCache\Entity::TEMPERATURE => '10',
			Model\WeatherCache\Entity::WINDSPEED => '25',
			Model\WeatherCache\Entity::WINDDEG => '302',
			Model\WeatherCache\Entity::HUMIDITY => '86',
			Model\WeatherCache\Entity::PRESSURE => '1033',
			Model\WeatherCache\Entity::WEATHERID => Weather\Condition::CLOUDY,
			Model\WeatherCache\Entity::WEATHER_SOURCE => Weather\Sources::OPENWEATHERMAP
		]);

		$Location = new Weather\Location();
		$Location->setTimestamp(1462289510);
		$Location->setGeohash('u1xjn3n74zxv');

		$this->object->loadForecast($Location);

		$this->assertTrue($this->object->wasSuccessfull());

		$Temperature = $this->object->temperature();
		$Temperature->toCelsius();

		$this->assertEquals(Weather\Condition::CLOUDY, $this->object->condition()->id());
		$this->assertEquals(25, $this->object->windSpeed()->value(), '', 0.01);
		$this->assertEquals(302, $this->object->windDegree()->value());
		$this->assertEquals(86, $this->object->humidity()->value());
		$this->assertEquals(1033, $this->object->pressure()->value());
		$this->assertEquals(10, $Temperature->value());
		$this->assertEquals(Weather\Sources::OPENWEATHERMAP, $this->object->sourceId());
	}

	public function testLoadForecastForTimeRange()
	{
		$this->insertCacheData([
			Model\WeatherCache\Entity::TIME => '1462289510',
			Model\WeatherCache\Entity::GEOHASH => 'u1xjn',
			Model\WeatherCache\Entity::TEMPERATURE => '10',
			Model\WeatherCache\Entity::WEATHERID => Weather\Condition::UNKNOWN
		]);

		$Location = new Weather\Location();
		$Location->setTimestamp(1462289510 - Weather\Forecast::TIME_PRECISION + 1);
		$Location->setGeohash('u1xjn3n74zxv');

		$this->object->loadForecast($Location);
		$this->assertTrue($this->object->wasSuccessfull());

		$Location->setTimestamp(1462289510 - Weather\Forecast::TIME_PRECISION - 1);

		$this->object->loadForecast($Location);
		$this->assertFalse($this->object->wasSuccessfull());

		$Location->setTimestamp(1462289510);
		$Location->setGeohash('u1xm2f8jc');

		$this->object->loadForecast($Location);
		$this->assertFalse($this->object->wasSuccessfull());
	}
}
