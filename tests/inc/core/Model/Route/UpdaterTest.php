<?php

namespace Runalyze\Model\Route;

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

	protected function setUp() {
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	protected function tearDown() {
		$this->PDO->exec('TRUNCATE `'.PREFIX.'route`');
	}

	public function testSimpleUpdate() {
		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(0);
		$Inserter->insert(new Entity(array(
			Entity::NAME => 'Route name',
			Entity::DISTANCE => 3.14
		)));

		$Route = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'route` WHERE `id`='.$Inserter->insertedID())->fetch(PDO::FETCH_ASSOC));
		$Route->set(Entity::DISTANCE, 0);

		$Changed = clone $Route;
		$Changed->set(Entity::NAME, 'New route name');

		$Updater = new Updater($this->PDO, $Changed, $Route);
		$Updater->setAccountID(0);
		$Updater->update();

		$Result = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'route` WHERE `id`='.$Inserter->insertedID())->fetch(PDO::FETCH_ASSOC));
		$this->assertEquals('New route name', $Result->name());
		$this->assertEquals(3.14, $Result->distance());
	}

}
