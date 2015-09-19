<?php

namespace Runalyze\Model;

use PDO;

class RelationUpdaterForObject_MockTester extends RelationUpdater {
	public $foobar;
	public function table() {
		return 'table';
	}
	public function selfColumn() {
		return 'self';
	}
	public function otherColumn() {
		return 'other';
	}
	protected function beforeUpdate() {
		$this->foobar .= 'before';
	}
	protected function afterUpdate() {
		$this->foobar .= 'after';
	}
}

class RelationUpdaterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'table` ( `self` INTEGER, `other` INTEGER )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'table`');
	}

	public function testThatNothingHappens() {
		$this->assertEmpty($this->PDO->query('SELECT * FROM `'.PREFIX.'table`')->fetchAll());

		$Updater = new RelationUpdaterForObject_MockTester($this->PDO, 1);
		$Updater->update();

		$this->assertEmpty($this->PDO->query('SELECT * FROM `'.PREFIX.'table`')->fetchAll());
		$this->assertEquals('beforeafter', $Updater->foobar);
	}

	public function testAddingRelations() {
		$Updater = new RelationUpdaterForObject_MockTester($this->PDO, 1);
		$Updater->update(array(1, 2, 3));

		$this->assertEquals(array(1, 2, 3), $this->PDO->query('SELECT `other` FROM `'.PREFIX.'table` WHERE `self`=1')->fetchAll(PDO::FETCH_COLUMN));
	}

	public function testRemovingRelations() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`self`, `other`) VALUES(1, 1)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`self`, `other`) VALUES(1, 2)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`self`, `other`) VALUES(1, 3)');

		$Updater = new RelationUpdaterForObject_MockTester($this->PDO, 1);
		$Updater->update(array(), array(1, 2, 3));

		$this->assertEquals(array(), $this->PDO->query('SELECT `other` FROM `'.PREFIX.'table` WHERE `self`=1')->fetchAll(PDO::FETCH_COLUMN));
	}

	public function testUpdatingRelations() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`self`, `other`) VALUES(1, 1)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`self`, `other`) VALUES(1, 2)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`self`, `other`) VALUES(2, 3)');

		$Updater = new RelationUpdaterForObject_MockTester($this->PDO, 1);
		$Updater->update(array(2, 3), array(1, 2));

		$this->assertEquals(array(2, 3), $this->PDO->query('SELECT `other` FROM `'.PREFIX.'table` WHERE `self`=1')->fetchAll(PDO::FETCH_COLUMN));
		$this->assertEquals(array(3), $this->PDO->query('SELECT `other` FROM `'.PREFIX.'table` WHERE `self`=2')->fetchAll(PDO::FETCH_COLUMN));
	}

}
