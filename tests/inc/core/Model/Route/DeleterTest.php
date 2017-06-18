<?php

namespace Runalyze\Model\Route;

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
	}

	protected function tearDown() {
		$this->PDO->exec('TRUNCATE TABLE `'.PREFIX.'route`');
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function insert(array $data) {
		$Inserter = new Inserter($this->PDO, new Entity($data));
		$Inserter->setAccountID(0);
		$Inserter->insert();

		return $Inserter->insertedID();
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
		return $this->PDO->query('SELECT * FROM `'.PREFIX.'route` WHERE `id`="'.$id.'" AND `accountid`=0')->fetch();
	}

	public function testWrongObject() {
	    if (PHP_MAJOR_VERSION >= 7) $this->setExpectedException('TypeError'); else $this->setExpectedException('\PHPUnit_Framework_Error');
		new Deleter($this->PDO, new \Runalyze\Model\Trackdata\Entity);
	}

	public function testSimpleDeletion() {
		$idToDelete = $this->insert(array(
			Entity::NAME => 'Route to go away'
		));
		$idToKeep = $this->insert(array(
			Entity::NAME => 'Route to stay'
		));
		$this->delete($idToDelete);

		$this->assertEquals(false, $this->fetch($idToDelete));
		$this->assertNotEquals(false, $this->fetch($idToKeep));
	}

}
