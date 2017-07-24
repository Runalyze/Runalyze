<?php

namespace Runalyze\Model;

use PDO;

class UpdaterWithAccountIDForObjectWithID_MockTester extends UpdaterWithIDAndAccountID {
	public function table() {
		return 'table';
	}
        protected function keys() {
            return array('foo', 'accountid');
        }
}
class UpdaterWithAccountIDObjectWithID_MockTester extends EntityWithID {
	public function properties() {
		return array ('foo', 'accountid');
	}
}

/**
 * @group requiresSqlite
 */
class UpdaterWithIDAndAccountIDTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'table` ( `id` INT(10), `foo` VARCHAR(10), `accountid` INT(10) )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'table`');
	}

	public function testUpdateOnlyCorrectOne() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `foo`, `accountid`) VALUES (1, "test", 1)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `foo`, `accountid`) VALUES (1, "test", 2)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `foo`, `accountid`) VALUES (2, "test", 1)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `foo`, `accountid`) VALUES (2, "test", 2)');

		$Updater = new UpdaterWithAccountIDForObjectWithID_MockTester($this->PDO, new UpdaterWithAccountIDObjectWithID_MockTester(array(
			'foo' => 'bar',
			'id' => 1
		)));
		$Updater->setAccountID(1);
		$Updater->update();

		$this->assertEquals(array(
			'foo' => 'bar'
		), $this->PDO->query('SELECT `foo` FROM `'.PREFIX.'table` WHERE `id`=1 AND `accountid`=1')->fetch(PDO::FETCH_ASSOC));
		$this->assertEquals(array('foo' => 'test'), $this->PDO->query('SELECT `foo` FROM `'.PREFIX.'table` WHERE `id`=1 AND `accountid`=2')->fetch(PDO::FETCH_ASSOC));
		$this->assertEquals(array('foo' => 'test'), $this->PDO->query('SELECT `foo` FROM `'.PREFIX.'table` WHERE `id`=2 AND `accountid`=1')->fetch(PDO::FETCH_ASSOC));
		$this->assertEquals(array('foo' => 'test'), $this->PDO->query('SELECT `foo` FROM `'.PREFIX.'table` WHERE `id`=2 AND `accountid`=2')->fetch(PDO::FETCH_ASSOC));
	}

}
