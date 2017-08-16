<?php

namespace Runalyze\Model\Equipment;

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
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment_type` (`name`, `accountid`) VALUES ("Test", 1)');

		$this->Typeid = $this->PDO->lastInsertId();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment_type`');
	}

	public function testSimpleUpdate() {
		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(1);
		$Inserter->insert(new Entity(array(
			Entity::NAME => 'Equipment name',
			Entity::TYPEID => $this->Typeid,
			Entity::NOTES => 'Here are some notes',
			Entity::DATE_START => '2015-01-01',
			Entity::DATE_END => '2015-02-02'
		)));

		$Type = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'equipment` WHERE `id`='.$Inserter->insertedID())->fetch(PDO::FETCH_ASSOC));
		$Type->set(Entity::NOTES, '');

		$Changed = clone $Type;
		$Changed->set(Entity::DATE_END, null);

		$Updater = new Updater($this->PDO, $Changed, $Type);
		$Updater->setAccountID(1);
		$Updater->update();

		$Result = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'equipment` WHERE `id`='.$Inserter->insertedID())->fetch(PDO::FETCH_ASSOC));

		$this->assertEquals('Equipment name', $Result->name());
		$this->assertEquals('Here are some notes', $Result->notes());
		$this->assertTrue($Result->hasStartDate());
		$this->assertEquals('2015-01-01', $Result->startDate());
		$this->assertTrue($Result->isInUse());
		$this->assertEquals(null, $Result->endDate());
	}

}
