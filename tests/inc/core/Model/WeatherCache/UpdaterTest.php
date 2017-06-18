<?php

namespace Runalyze\Model\WeatherCache;

use PDO;
use DB;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase {

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
	}

	public function testSimpleUpdate() {
		$Inserter = new Inserter($this->PDO);
		$Inserter->insert(new Entity(array(
			Entity::TIME => '1462289520',
			Entity::GEOHASH => 'u1xjn',
			Entity::TEMPERATURE => '10',
			Entity::WEATHERID => '5'
		)));

		$WeatherCache = new Entity($this->PDO->query("SELECT * FROM `".PREFIX."weathercache` WHERE time='1462289520' AND geohash='u1xjn'")->fetch(PDO::FETCH_ASSOC));
		
		
		$Changed = clone $WeatherCache;
		$Changed->set(Entity::TEMPERATURE, 20);
		
		$Updater = new Updater($this->PDO, $Changed, $WeatherCache);
		$Updater->update();

		$Result = new Entity($this->PDO->query("SELECT * FROM `".PREFIX."weathercache` WHERE time='1462289520' AND geohash='u1xjn'")->fetch(PDO::FETCH_ASSOC));

		$this->assertEquals(20, $Result->temperature());

	}

}
