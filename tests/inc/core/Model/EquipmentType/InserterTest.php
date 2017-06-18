<?php

namespace Runalyze\Model\EquipmentType;

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

	protected function setUp() {
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment_type`');
	}

	public function testWrongObject() {
	    if (PHP_MAJOR_VERSION >= 7) $this->setExpectedException('TypeError'); else $this->setExpectedException('\PHPUnit_Framework_Error');
		new Inserter($this->PDO, new InvalidInserterObjectForType_MockTester);
	}

	public function testSimpleInsert() {
		$Type = new Entity(array(
			Entity::NAME => 'Equipment type name',
			Entity::INPUT => 0,
			Entity::MAX_KM => 100,
			Entity::MAX_TIME => 0
		));

		$Inserter = new Inserter($this->PDO, $Type);
		$Inserter->setAccountID(1);
		$Inserter->insert();

		$data = $this->PDO->query('SELECT * FROM `'.PREFIX.'equipment_type` WHERE `accountid`=1')->fetch(PDO::FETCH_ASSOC);
		$New = new Entity($data);

		$this->assertEquals('Equipment type name', $New->name());
		$this->assertFalse($New->allowsMultipleValues());
		$this->assertTrue($New->hasMaxDistance());
		$this->assertEquals(100, $New->maxDistance());
		$this->assertFalse($New->hasMaxDuration());
		$this->assertEquals(0, $New->maxDuration());
	}

}
