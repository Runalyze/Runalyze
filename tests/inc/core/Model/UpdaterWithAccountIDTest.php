<?php

namespace Runalyze\Model;

use PDO;

class UpdaterWithAccountIDForObject_MockTester extends UpdaterWithAccountID {
	public function table() {
		return 'table';
	}
	public function whereSubclass() {
		return '`key`="1"';
	}
        protected function keys() {
            return array('foo', 'accountid');
        }
}
class UpdaterWithAccountIDObject_MockTester extends Entity {
	public function properties() {
		return array ('foo', 'accountid');
	}
}

/**
 * @group requiresSqlite
 */
class UpdaterWithAccountIDTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'table` ( `key` VARCHAR(1), `foo` VARCHAR(10), `accountid` INT(10) )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'table`');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testWithoutAccountID() {
		$Updater = new UpdaterWithAccountIDForObject_MockTester($this->PDO);
		$Updater->update(new UpdaterWithAccountIDObject_MockTester());
	}

	public function testUpdateOnlyCurrentAccountID() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`key`, `foo`, `accountid`) VALUES ("1", "test", 1)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`key`, `foo`, `accountid`) VALUES ("1", "test", 2)');

		$NewObject = new UpdaterWithAccountIDObject_MockTester(array(
			'foo' => 'bar'
		));

		$Updater = new UpdaterWithAccountIDForObject_MockTester($this->PDO);
		$Updater->setAccountID(1);
		$Updater->update($NewObject);

		$this->assertEquals(array(
			'key' => '1',
			'foo' => 'bar'
		), $this->PDO->query('SELECT `key`, `foo` FROM `'.PREFIX.'table` WHERE `accountid`=1')->fetch(PDO::FETCH_ASSOC));
		$this->assertEquals(array(
			'key' => '1',
			'foo' => 'test'
		), $this->PDO->query('SELECT `key`, `foo` FROM `'.PREFIX.'table` WHERE `accountid`=2')->fetch(PDO::FETCH_ASSOC));
	}

}
