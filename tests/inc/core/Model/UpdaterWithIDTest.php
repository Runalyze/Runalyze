<?php

namespace Runalyze\Model;

use PDO;

class UpdaterForObjectWithID_MockTester extends UpdaterWithID {
	public function table() {
		return 'table';
	}
        protected function keys() {
            return array('foo');
        }
}
class UpdaterObjectWithID_MockTester extends EntityWithID {
	public function properties() {
		return array ('foo');
	}
}
class InvalidUpdaterObjectWithID_MockTester extends Entity {
	public function properties() {
		return array ('foo');
	}
}

/**
 * @group requiresSqlite
 */
class UpdaterWithIDTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'table` ( `id` INT(10), `foo` VARCHAR(10) )');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'table`');
	}

	public function testWrongObject() {
	    if (PHP_MAJOR_VERSION >= 7) $this->setExpectedException('TypeError'); else $this->setExpectedException('\PHPUnit_Framework_Error');
		new UpdaterForObjectWithID_MockTester($this->PDO, new InvalidUpdaterObjectWithID_MockTester);
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testWithoutID() {
		$Updater = new UpdaterForObjectWithID_MockTester($this->PDO, new UpdaterObjectWithID_MockTester(array(
			'foo' => 'bar'
		)));
		$Updater->update();
	}

	public function testUpdateOnlyID() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `foo`) VALUES (1, "test")');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'table` (`id`, `foo`) VALUES (2, "test")');

		$Updater = new UpdaterForObjectWithID_MockTester($this->PDO, new UpdaterObjectWithID_MockTester(array(
			'foo' => 'bar',
			'id' => 1
		)));
		$Updater->update();

		$this->assertEquals(array(
			'foo' => 'bar'
		), $this->PDO->query('SELECT `foo` FROM `'.PREFIX.'table` WHERE `id`=1')->fetch(PDO::FETCH_ASSOC));
		$this->assertEquals(array(
			'foo' => 'test'
		), $this->PDO->query('SELECT `foo` FROM `'.PREFIX.'table` WHERE `id`=2')->fetch(PDO::FETCH_ASSOC));
	}

}
