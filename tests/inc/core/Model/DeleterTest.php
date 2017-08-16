<?php

namespace Runalyze\Model;

use PDO;

class Deleter_MockTester extends DeleterWithAccountID {
	public function table() {
		return 'table';
	}
	protected function where() {
		return '`foo`="DELETE"';
	}
	protected function before() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'log` (`msg`) VALUES ("before")');
	}
	protected function after() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'log` (`msg`) VALUES ("after")');
	}
}
class DeleterObject_MockTester extends Entity {
	public function properties() {
		return array ('foo');
	}
}

/**
 * @group requiresSqlite
 */
class DeleterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'table` ( `foo` VARCHAR(10) )');
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'log` ( `msg` VARCHAR(10) )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'table`');
		$this->PDO->exec('DROP TABLE `'.PREFIX.'log`');
	}

	public function testDeleteOnlyCorrectOne() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`foo`) VALUES ("bar")');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`foo`) VALUES ("DELETE")');

		$Deleter = new Deleter_MockTester($this->PDO, new DeleterObject_MockTester());
		$Deleter->delete();

		$this->assertEquals(array('bar'), $this->PDO->query('SELECT `foo` FROM `'.PREFIX.'table`')->fetchAll(PDO::FETCH_COLUMN));
		$this->assertEquals(array('before', 'after'), $this->PDO->query('SELECT `msg` FROM `'.PREFIX.'log`')->fetchAll(PDO::FETCH_COLUMN));
	}

	public function testThatAfterIsNotTriggeredIfNothingIsDeleted() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`foo`) VALUES ("bar")');

		$Deleter = new Deleter_MockTester($this->PDO, new DeleterObject_MockTester());
		$Deleter->delete();

		$this->assertEquals(array('before'), $this->PDO->query('SELECT `msg` FROM `'.PREFIX.'log`')->fetchAll(PDO::FETCH_COLUMN));
	}

}
