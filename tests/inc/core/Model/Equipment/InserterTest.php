<?php

namespace Runalyze\Model\Equipment;

use PDO;
use DB;

class InvalidInserterObjectForType_MockTester extends \Runalyze\Model\Entity {
	public function properties() {
		return array('foo');
	}
}

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
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment_type` (`name`, `accountid`) VALUES ("Test", 1)');

		$this->Typeid = $this->PDO->lastInsertId();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment_type`');
	}

	public function testWrongObject() {
	    if (PHP_MAJOR_VERSION >= 7) $this->setExpectedException('TypeError'); else $this->setExpectedException('\PHPUnit_Framework_Error');
		new Inserter($this->PDO, new InvalidInserterObjectForType_MockTester);
	}

	public function testSimpleInsert() {
		$Equipment = new Entity(array(
			Entity::NAME => 'Equipment name',
			Entity::TYPEID => $this->Typeid,
			Entity::NOTES => 'Here are some notes',
			Entity::DATE_START => '2015-01-01',
			Entity::DATE_END => null
		));

		$Inserter = new Inserter($this->PDO, $Equipment);
		$Inserter->setAccountID(1);
		$Inserter->insert();

		$data = $this->PDO->query('SELECT * FROM `'.PREFIX.'equipment` WHERE `accountid`=1')->fetch(PDO::FETCH_ASSOC);
		$New = new Entity($data);

		$this->assertEquals('Equipment name', $New->name());
		$this->assertEquals('Here are some notes', $New->notes());
		$this->assertTrue($New->hasStartDate());
		$this->assertEquals('2015-01-01', $New->startDate());
		$this->assertTrue($New->isInUse());
		$this->assertEquals(null, $New->endDate());
	}

}
