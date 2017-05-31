<?php

namespace Runalyze\Model\Swimdata;

use PDO;
use DB;

class InvalidInserterObjectForSwimdata_MockTester extends \Runalyze\Model\Entity {
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

	protected $ActivityID;

	protected function setUp() {
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('INSERT INTO `runalyze_training` (`accountid`, `sportid`, `time`, `s`) VALUES (0, 0, 1477843525, 2)');

		$this->ActivityID = $this->PDO->lastInsertId();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `runalyze_training`');
	}

	public function testWrongObject() {
	    if (PHP_MAJOR_VERSION >= 7) $this->setExpectedException('TypeError'); else $this->setExpectedException('\PHPUnit_Framework_Error');
		new Inserter($this->PDO, new InvalidInserterObjectForSwimdata_MockTester);
	}

	public function testSimpleInsert() {
		$R = new Entity(array(
			Entity::ACTIVITYID => $this->ActivityID,
			Entity::POOL_LENGTH => 2500,
			Entity::STROKE => array(25, 20, 15, 20),
			Entity::STROKETYPE => array(2, 2, 2, 2)
		));

		$I = new Inserter($this->PDO, $R);
		$I->setAccountID(0);
		$I->insert();

		$data = $this->PDO->query('SELECT * FROM `runalyze_swimdata` WHERE `activityid`='.$this->ActivityID)->fetch(PDO::FETCH_ASSOC);
		$N = new Entity($data);

		$this->assertEquals(0, $data[Inserter::ACCOUNTID]);
		$this->assertEquals(2500, $N->poollength());
		$this->assertEquals(array(25, 20, 15, 20), $N->stroke());
		$this->assertEquals(array(2, 2, 2, 2), $N->stroketype());
	}

}
