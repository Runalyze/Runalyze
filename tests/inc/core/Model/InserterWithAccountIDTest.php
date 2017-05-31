<?php

namespace Runalyze\Model;

use PDO;

class InserterWithAccountID_MockTester extends InserterWithAccountID {
	public function table() {
		return 'temp';
	}
	public function keys() {
		return array('foo', 'accountid');
	}
}
class InserterWithAccountIDObject_MockTester extends Entity {
	public function properties() {
		return array ('foo');
	}
}

class InvalidInserterWithAccountID_MockTester extends InserterWithAccountID {
	public function table() {
		return 'temp';
	}
	public function keys() {
		return array('foo');
	}
}

/**
 * @group requiresSqlite
 */
class InserterWithAccountIDTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'temp` ( `foo` VARCHAR(10), `accountid` INT )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'temp`');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testWithoutAccountID() {
		$O = new InserterWithAccountIDObject_MockTester();
		$I = new InserterWithAccountID_MockTester($this->PDO, $O);
		$I->insert();
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testInvalidClass() {
		$O = new InserterWithAccountIDObject_MockTester();
		$I = new InvalidInserterWithAccountID_MockTester($this->PDO, $O);
		$I->setAccountID(1);
		$I->insert();
	}

	public function testSimpleInsert() {
		$O = new InserterWithAccountIDObject_MockTester(array('foo' => 'bar'));
		$I = new InserterWithAccountID_MockTester($this->PDO, $O);
		$I->setAccountID(1);
		$I->insert();

		$this->assertEquals(array(
			'foo' => 'bar',
			'accountid' => 1
		), $this->PDO->query('SELECT * FROM `'.PREFIX.'temp`')->fetch(PDO::FETCH_ASSOC));
	}
        
}
