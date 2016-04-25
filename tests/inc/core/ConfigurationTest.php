<?php

namespace Runalyze;

use DB;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

	/** @var \PDO */
	protected $PDO;

	public function setUp()
	{
		$this->PDO = DB::getInstance();
		$this->PDO->exec('DELETE FROM `'.PREFIX.'conf`');
	}

	public function tearDown()
	{
		$this->PDO->exec('DELETE FROM `'.PREFIX.'conf`');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testThatResettingRequiresAccountId()
	{
		Configuration::loadAll(null);
		Configuration::resetConfiguration();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidAccountId()
	{
		Configuration::resetConfiguration('foo');
	}

	public function testResetConfiguration()
	{
		Configuration::loadAll(0);
		Configuration::loadAll(1);

		$this->PDO->exec('UPDATE `'.PREFIX.'conf` SET `value`="12345" WHERE `key`="PLZ" AND `accountid`=0');
		$this->PDO->exec('UPDATE `'.PREFIX.'conf` SET `value`="42" WHERE `key`="VDOT_FORM" AND `accountid`=0');
		$this->PDO->exec('UPDATE `'.PREFIX.'conf` SET `value`="56789" WHERE `key`="PLZ" AND `accountid`=1');

		Configuration::loadAll(0);

		$this->assertEquals('12345', Configuration::ActivityForm()->weatherLocation());
		$this->assertEquals('42', Configuration::Data()->vdotShape());

		Configuration::resetConfiguration(0);
		Configuration::loadAll(0);

		$this->assertEquals('', Configuration::ActivityForm()->weatherLocation());
		$this->assertEquals('42', Configuration::Data()->vdotShape());

		Configuration::loadAll(1);

		$this->assertEquals('56789', Configuration::ActivityForm()->weatherLocation());
	}

}
