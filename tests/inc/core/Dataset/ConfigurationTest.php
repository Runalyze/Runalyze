<?php

namespace Runalyze\Dataset;

use PDO;
use Cache;

/**
 * @group dataset
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

	/** @var \PDO */
	protected $PDO;

	/** @var \Runalyze\Dataset\Configuration */
	protected $Configuration;

	public function setUp()
	{
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TEMP TABLE `'.PREFIX.'dataset` ( `accountid` INTEGER, `keyid` INTEGER, `position` INTEGER, `active` INTEGER, `style` VARCHAR(10) )');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'dataset` VALUES(0, 1, 7, 1, "")');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'dataset` VALUES(0, 2, 2, 1, "test")');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'dataset` VALUES(0, 3, 9, 0, "")');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'dataset` VALUES(1, 4, 3, 1, "")');

		$this->Configuration = new Configuration($this->PDO, 0);
	}

	public function tearDown()
	{
		$this->PDO->exec('DROP TABLE `'.PREFIX.'dataset`');
	}

	public function testEmptiness()
	{
		$this->assertFalse($this->Configuration->isEmpty());

		$this->assertTrue((new Configuration($this->PDO, 2, false))->isEmpty());
	}

	public function testFallbackToDefault()
	{
		$this->assertFalse((new Configuration($this->PDO, 42))->isEmpty());
	}

	public function testExistance()
	{
		$this->assertFalse($this->Configuration->exists(0));
		$this->assertTrue($this->Configuration->exists(1));
		$this->assertTrue($this->Configuration->exists(2));
		$this->assertTrue($this->Configuration->exists(3));
		$this->assertFalse($this->Configuration->exists(4));
		$this->assertFalse($this->Configuration->exists(5));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidKeyForActive()
	{
		$this->Configuration->isActive(5);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidKeyForStyle()
	{
		$this->Configuration->getStyle(5);
	}

	public function testAllKeys()
	{
		$this->assertEquals(
			array(2, 1, 3),
			$this->Configuration->allKeys()
		);
	}

	public function testActiveKeys()
	{
		$this->assertEquals(
			array(2, 1),
			$this->Configuration->activeKeys()
		);
	}

	public function testKeySpecificMethods()
	{
		$this->assertEquals(true, $this->Configuration->isActive(1));
		$this->assertEquals(true, $this->Configuration->isActive(2));
		$this->assertEquals(false, $this->Configuration->isActive(3));

		$this->assertEquals('', $this->Configuration->getStyle(1));
		$this->assertEquals('test', $this->Configuration->getStyle(2));
		$this->assertEquals('', $this->Configuration->getStyle(3));
	}

}
