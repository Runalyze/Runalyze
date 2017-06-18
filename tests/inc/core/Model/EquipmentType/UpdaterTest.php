<?php

namespace Runalyze\Model\EquipmentType;

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
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment_type`');
	}

	public function testSimpleUpdate() {
		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(1);
		$Inserter->insert(new Entity(array(
			Entity::NAME => 'Equipment type name',
			Entity::INPUT => 1,
			Entity::MAX_KM => 100,
			Entity::MAX_TIME => 0
		)));

		$Type = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'equipment_type` WHERE `id`='.$Inserter->insertedID())->fetch(PDO::FETCH_ASSOC));
		$Type->set(Entity::INPUT, 0);

		$Changed = clone $Type;
		$Changed->set(Entity::MAX_TIME, 500);

		$Updater = new Updater($this->PDO, $Changed, $Type);
		$Updater->setAccountID(1);
		$Updater->update();

		$Result = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'equipment_type` WHERE `id`='.$Inserter->insertedID())->fetch(PDO::FETCH_ASSOC));
		$this->assertEquals('Equipment type name', $Result->name());
		$this->assertTrue($Result->allowsMultipleValues());
		$this->assertTrue($Result->hasMaxDistance());
		$this->assertTrue($Result->hasMaxDuration());
		$this->assertEquals(100, $Result->maxDistance());
		$this->assertEquals(500, $Result->maxDuration());
	}

}
