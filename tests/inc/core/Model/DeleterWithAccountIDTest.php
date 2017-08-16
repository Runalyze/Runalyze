<?php

namespace Runalyze\Model;

use PDO;

class DeleterWithAccountIDForObject_MockTester extends DeleterWithAccountID {
	public function table() {
		return 'table';
	}
	protected function where() {
		return '`delete`=1 AND '.parent::where();
	}
}
class DeleterWithAccountIDObject_MockTester extends Entity {
	public function properties() {
		return array ('delete', 'accountid');
	}
}

/**
 * @group requiresSqlite
 */
class DeleterWithAccountIDTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'table` ( `id` INT(10), `delete` INT(10), `accountid` INT(10) )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'table`');
	}

	public function testDeleteOnlyCorrectOne() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `delete`, `accountid`) VALUES (1, 1, 1)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `delete`, `accountid`) VALUES (2, 1, 2)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `delete`, `accountid`) VALUES (3, 0, 1)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `delete`, `accountid`) VALUES (4, 0, 2)');

		$Deleter = new DeleterWithAccountIDForObject_MockTester($this->PDO, new DeleterWithAccountIDObject_MockTester());
		$Deleter->setAccountID(1);
		$Deleter->delete();

		$this->assertEquals(array(2, 3, 4), $this->PDO->query('SELECT `id` FROM `'.PREFIX.'table`')->fetchAll(PDO::FETCH_COLUMN));
	}

}
