<?php

namespace Runalyze\Model\WeatherCache;

use PDO;
use DB;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class InserterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * @var int
	 */
	protected $Typeid;

	protected function setUp() {
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'weathercache`');
	}

	public function testSimpleInsert() {
		$WeatherCache = new Entity(array(
			Entity::TIME => '1462289510',
			Entity::GEOHASH => 'u1xjn',
			Entity::TEMPERATURE => '10',
			Entity::WINDSPEED => '25',
			Entity::WINDDEG => '302',
			Entity::HUMIDITY => '86',
			Entity::PRESSURE => '1033',
			Entity::WEATHERID => '5',
			Entity::WEATHER_SOURCE => '1'
		));

		$Inserter = new Inserter($this->PDO, $WeatherCache);
		$Inserter->insert();

		$data = $this->PDO->query("SELECT * FROM `".PREFIX."weathercache` WHERE time='".$WeatherCache->time()."' AND geohash='".$WeatherCache->geohash()."'")->fetch(PDO::FETCH_ASSOC);
		$New = new Entity($data);

		$this->assertEquals(1462289510, $New->time());
		$this->assertEquals('u1xjn', $New->geohash());
		$this->assertEquals(10, $New->temperature());
		$this->assertEquals(25, $New->windSpeed());
		$this->assertEquals(302, $New->windDegree());
		$this->assertEquals(86, $New->humidity());
		$this->assertEquals(1033, $New->pressure());
		$this->assertEquals(5, $New->weatherid());
		$this->assertEquals(1, $New->weatherSource());

	}

}
