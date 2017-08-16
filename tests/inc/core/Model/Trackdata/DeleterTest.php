<?php

namespace Runalyze\Model\Trackdata;

use PDO;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class DeleterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = \DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `runalyze_training`');
	}

	/**
	 * @param array $data
	 */
	protected function insert(array $data) {
		$Inserter = new Inserter($this->PDO, new Entity($data));
		$Inserter->setAccountID(0);
		$Inserter->insert();
	}

	/**
	 * @param int $id
	 */
	protected function delete($id) {
		$Deleter = new Deleter($this->PDO, new Entity($this->fetch($id)));
		$Deleter->setAccountID(0);
		$Deleter->delete();
	}

	/**
	 * @param int $id
	 * @return mixed
	 */
	protected function fetch($id) {
		return $this->PDO->query('SELECT * FROM `runalyze_trackdata` WHERE `activityid`="'.$id.'" AND `accountid`=0')->fetch();
	}

	public function testWrongObject() {
	    if (PHP_MAJOR_VERSION >= 7) $this->setExpectedException('TypeError'); else $this->setExpectedException('\PHPUnit_Framework_Error');
		new Deleter($this->PDO, new \Runalyze\Model\Route\Entity);
	}

	public function testSimpleDeletion() {
		$this->PDO->exec('INSERT INTO `runalyze_training` (`accountid`, `sportid`, `time`, `s`) VALUES (0, 0, 1477843525, 2)');
		$firstID = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `runalyze_training` (`accountid`, `sportid`, `time`, `s`) VALUES (0, 0, 1477843525, 2)');
		$secondID = $this->PDO->lastInsertId();

		$this->insert(array(
			Entity::ACTIVITYID => $firstID
		));
		$this->insert(array(
			Entity::ACTIVITYID => $secondID
		));
		$this->delete($firstID);

		$this->assertEquals(false, $this->fetch($firstID));
		$this->assertNotEquals(false, $this->fetch($secondID));
	}

}
