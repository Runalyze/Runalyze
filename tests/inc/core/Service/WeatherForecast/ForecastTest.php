<?php

namespace Runalyze\Service\WeatherForecast;

use Runalyze\Data\Weather;
use Runalyze\Model\WeatherCache;

class ForecastTest extends \PHPUnit_Framework_TestCase
{

	public function testOpenweathermapEmptyLocation()
	{
		$Forecast = new Forecast(new Weather\Location, new Strategy\Openweathermap);
		$object = $Forecast->object();

		$this->assertTrue( $object->isEmpty() );
		$this->assertNull($object->source());
	}

	public function testOpenweathermapLocationByName()
	{
		$Location = new Weather\Location();
		$Location->setLocationName('Berlin, de');

		$Forecast = new Forecast($Location, new Strategy\Openweathermap);
		$object = $Forecast->object();

		if ($object->isEmpty()) {
			$this->markTestSkipped('Openweathermap: "Berlin, de" was not available.');
		} else {
			$this->assertEquals(Weather\Sources::OPENWEATHERMAP, $object->source());
		}
	}

	public function testOpenweathermapLocationByTime()
	{
		// Historical data, '< 1 month (list of available cities is limited)'
		// @see http://bugs.openweathermap.org/projects/api/wiki/Api_2_5_history
		// @see http://openweathermap.org/price
		$Location = new Weather\Location();
		$Location->setLocationName('Berlin, de');
		$Location->setDateTime( (new \DateTime())->setTimestamp(time() - 28*86500));

		$Forecast = new Forecast($Location, new Strategy\Openweathermap);
		$object = $Forecast->object();

		if ($object->isEmpty()) {
			$this->markTestSkipped('Openweathermap: History for "Berlin, de" was not available.');
		} else {
			$this->assertEquals(Weather\Sources::OPENWEATHERMAP, $object->source());
		}
	}

	public function testOpenweathermapLocationByPosition()
	{
		$Location = new Weather\Location();
		$Location->setPosition(49.9, 7.77);

		$Forecast = new Forecast($Location, new Strategy\Openweathermap);
		$object = $Forecast->object();

		if ($object->isEmpty()) {
			$this->markTestSkipped('Openweathermap: Position "49,9, 7.77" was not available.');
		} else {
			$this->assertEquals(Weather\Sources::OPENWEATHERMAP, $object->source());
		}
	}

}
