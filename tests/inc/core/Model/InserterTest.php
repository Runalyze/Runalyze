<?php

namespace Runalyze\Model;

use PDO;

class InserterForObjectWithoutID_MockTester extends Inserter {
	public function table() {
		return 'withoutid';
	}
	protected function keys() {
		return array('foo','arr');
	}
}
class InserterObjectWithoutID_MockTester extends Entity {
	public function properties() {
		return array ('foo', 'arr');
	}
	public function isArray($key) {
		return ($key == 'arr');
	}
}

class InserterForObjectWithID_MockTester extends Inserter {
	public function table() {
		return 'withid';
	}
	public function keys() {
		return array('foo');
	}
}
class InserterObjectWithID_MockTester extends EntityWithID {
	public function properties() {
		return array('foo', 'bar');
	}
}

class InserterForSquarObject_MockTester extends Inserter {
	public function table() {
		return 'withoutid';
	}
	protected function before() {
		$this->Object->set('foo', pow($this->Object->get('foo'),2));
	}
	protected function after() {
		$this->Object->set('foo', sqrt($this->Object->get('foo')));
	}
        protected function keys() {
            return array('foo');
        }

}
class InserterSquareObject_MockTester extends Entity {
	public function properties() {
		return array('foo');
	}
}

/**
 * @group requiresSqlite
 */
class InserterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'withoutid` ( `foo` VARCHAR(10), `arr` VARCHAR(100) )');
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'withid` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `foo` VARCHAR(10) )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'withoutid`');
		$this->PDO->exec('DROP TABLE `'.PREFIX.'withid`');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testInsertedIDexception() {
		$O = new InserterObjectWithoutID_MockTester();
		$I = new InserterForSquarObject_MockTester($this->PDO, $O);
		$I->insert();
		$I->insertedID();
	}

	/**
	 * Table 'withoutid'
	 */
	public function testWithoutID_simpleInsert() {
		$data = array('foo' => 'bar', 'arr' => array(1,2,3));
		$O1 = new InserterObjectWithoutID_MockTester($data);
		$I = new InserterForObjectWithoutID_MockTester($this->PDO);
		$I->insert($O1);

		$this->assertEquals(1, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withoutid`')->fetchColumn());
		$this->assertEquals(0, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withid`')->fetchColumn());

		$fetchedData = $this->PDO->query('SELECT * FROM `'.PREFIX.'withoutid`')->fetch(PDO::FETCH_ASSOC);
		$O2 = new InserterObjectWithoutID_MockTester($fetchedData);

		$this->assertEquals($data, $O2->completeData());
	}

	public function testWithoutID_preparedInsert() {
		$data1 = array('foo' => 'bar', 'arr' => '123');
		$data2 = array('foo' => 'pi', 'arr' => '456');
		$O1 = new InserterObjectWithoutID_MockTester($data1);
		$O2 = new InserterObjectWithoutID_MockTester($data2);
		$I = new InserterForObjectWithoutID_MockTester($this->PDO, $O1);
		$I->prepare();
		$I->insert($O1);
		$I->insert($O2);
		$I->insert($O2);

		$this->assertEquals(3, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withoutid`')->fetchColumn());
		$this->assertEquals(0, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withid`')->fetchColumn());

		$this->assertEquals(array(
			$data1,
			$data2,
			$data2
		), $this->PDO->query('SELECT * FROM `'.PREFIX.'withoutid`')->fetchAll(PDO::FETCH_ASSOC));
	}

	public function testBeforeAndAfterMethods() {
		$O = new InserterSquareObject_MockTester(array('foo' => 3));
		$I = new InserterForSquarObject_MockTester($this->PDO, $O);
		$I->insert();

		$this->assertEquals(3, $O->get('foo'));
		$this->assertEquals(9, $this->PDO->query('SELECT `foo` FROM `'.PREFIX.'withoutid`')->fetchColumn());
	}

	public function testWithoutID_sqlInjection() {
		$I = new InserterForObjectWithoutID_MockTester($this->PDO);
		$I->insert(new InserterObjectWithoutID_MockTester(array(
			'arr' => array('a");INSERT INTO `'.PREFIX.'withid` (`foo`) VALUES ("hack')
		)));

		$this->assertEquals(1, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withoutid`')->fetchColumn());
		$this->assertEquals(0, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withid`')->fetchColumn());

		$this->assertEquals(array(
			'foo' => "",
			'arr' => 'a");INSERT INTO `'.PREFIX.'withid` (`foo`) VALUES ("hack'
		), $this->PDO->query('SELECT * FROM `'.PREFIX.'withoutid`')->fetch(PDO::FETCH_ASSOC));
	}

	/**
	 * Table 'withid'
	 */
	public function testWithID_simpleInsert() {
		$data = array('foo' => 'pi', 'bar' => '42');
		$O = new InserterObjectWithID_MockTester($data);
		$I = new InserterForObjectWithID_MockTester($this->PDO, $O);
		$I->insert();

		$this->assertEquals(1, $I->insertedID());
		$this->assertEquals(0, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withoutid`')->fetchColumn());
		$this->assertEquals(1, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withid`')->fetchColumn());

		$this->assertEquals(array(
			'id' => 1,
			'foo' => 'pi'
		), $this->PDO->query('SELECT * FROM `'.PREFIX.'withid`')->fetch(PDO::FETCH_ASSOC));

		$I->insert();
		$this->assertEquals(2, $I->insertedID());
		$I->insert();
		$this->assertEquals(3, $I->insertedID());
	}

	public function testWithID_preparedInsert() {
		$O1 = new InserterObjectWithID_MockTester(array('foo' => 'bar'));
		$O2 = new InserterObjectWithID_MockTester(array('foo' => 'whiskey'));
		$O3 = new InserterObjectWithID_MockTester(array('foo' => 'in the jar'));
		$I = new InserterForObjectWithID_MockTester($this->PDO);
		$I->prepare();
		$I->insert($O1);
		$I->insert($O2);
		$I->insert($O3);

		$this->assertEquals(0, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withoutid`')->fetchColumn());
		$this->assertEquals(3, $this->PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'withid`')->fetchColumn());

		$this->assertEquals(array(
			array('id' => 1, 'foo' => 'bar'),
			array('id' => 2, 'foo' => 'whiskey'),
			array('id' => 3, 'foo' => 'in the jar')
		), $this->PDO->query('SELECT * FROM `'.PREFIX.'withid`')->fetchAll(PDO::FETCH_ASSOC));
	}

}
