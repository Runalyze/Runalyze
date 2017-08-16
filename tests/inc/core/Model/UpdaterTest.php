<?php

namespace Runalyze\Model;

use PDO;

class UpdaterForObject_MockTester extends Updater {
	public function table() {
		return 'table';
	}
	public function where() {
		return '`key`="1"';
	}
        protected function keys() {
            return array('foo','bar');
        }
}
class UpdaterObject_MockTester extends Entity {
	public function properties() {
		return array ('foo', 'bar');
	}
}

/**
 * @group requiresSqlite
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'table` ( `key` VARCHAR(1), `foo` VARCHAR(10), `bar` VARCHAR(10) )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'table`');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testUpdateWithoutObject() {
		$Updater = new UpdaterForObject_MockTester($this->PDO);
		$Updater->update();
	}

	public function testSimpleUpdate() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`key`, `foo`, `bar`) VALUES ("1", "test", "bar")');

		$NewObject = new UpdaterObject_MockTester(array(
			'foo' => 'foo',
			'bar' => 'test'
		));

		$Updater = new UpdaterForObject_MockTester($this->PDO);
		$Updater->update($NewObject);

		$this->assertEquals(array(
			'key' => '1',
			'foo' => 'foo',
			'bar' => 'test'
		), $this->PDO->query('SELECT `key`, `foo`, `bar` FROM `'.PREFIX.'table`')->fetch(PDO::FETCH_ASSOC));
	}

	public function testUpdateForOnlyNewValues() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`key`, `foo`, `bar`) VALUES ("1", "", "bar")');

		$OldObject = new UpdaterObject_MockTester(array(
			'foo' => 'foo',
			'bar' => 'bar'
		));

		$NewObject = new UpdaterObject_MockTester(array(
			'foo' => 'foo',
			'bar' => 'test'
		));

		$Updater = new UpdaterForObject_MockTester($this->PDO, $NewObject, $OldObject);
		$Updater->update();

		$this->assertEquals(array(
			'key' => '1',
			'foo' => '',
			'bar' => 'test'
		), $this->PDO->query('SELECT `key`, `foo`, `bar` FROM `'.PREFIX.'table`')->fetch(PDO::FETCH_ASSOC));
	}

	public function testSpecificKeys() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`key`, `foo`, `bar`) VALUES ("1", "", "")');

		$Updater = new UpdaterForObject_MockTester($this->PDO);
		$Updater->update(new UpdaterObject_MockTester(array(
			'foo' => 'test',
			'bar' => 'test'
		)), array(
			'foo'
		));

		$this->assertEquals(array(
			'key' => '1',
			'foo' => 'test',
			'bar' => ''
		), $this->PDO->query('SELECT `key`, `foo`, `bar` FROM `'.PREFIX.'table`')->fetch(PDO::FETCH_ASSOC));
	}

	public function testEmptySpecificKeys() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`key`, `foo`, `bar`) VALUES ("1", "", "")');

		$Updater = new UpdaterForObject_MockTester($this->PDO);
		$Updater->update(new UpdaterObject_MockTester(array(
			'foo' => 'test',
			'bar' => 'test'
		)), array());

		$this->assertEquals(array(
			'key' => '1',
			'foo' => '',
			'bar' => ''
		), $this->PDO->query('SELECT `key`, `foo`, `bar` FROM `'.PREFIX.'table`')->fetch(PDO::FETCH_ASSOC));
	}

}
