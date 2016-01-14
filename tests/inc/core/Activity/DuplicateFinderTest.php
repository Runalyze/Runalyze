<?php

namespace Runalyze\Activity;

use PDO;

class DuplicateFinderTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PDO */
	protected $PDO;

	/** @var \Runalyze\Activity\DuplicateFinder */
	protected $Finder;

	public function setUp()
	{
		$this->PDO = \DB::getInstance();
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`activity_id`, `accountid`) VALUES (1448797800, 0)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`activity_id`, `accountid`) VALUES (1450823800, 0)');

		$this->Finder = new DuplicateFinder($this->PDO, 0);
	}

	public function tearDown()
	{
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
	}

	public function testDuplicate()
	{
	    $this->assertTrue($this->Finder->checkForDuplicate(1448797800));
	}

	public function testNewActivity()
	{
	    $this->assertFalse($this->Finder->checkForDuplicate(1234567890));
	}

	/** @expectedException \InvalidArgumentException */
	public function testInvalidArgument()
	{
	    $this->Finder->checkForDuplicate('foobar');
	}

	public function testDuplicates()
	{
	    $this->assertEquals([
	    		'1234567890' => false,
	    		'1448797800' => true,
	    		'1448797980' => false,
	    		'1450823800' => true,
	    		'9876543210' => false
	    	], $this->Finder->checkForDuplicates([
	    		1234567890,
	    		1448797800,
	    		1448797980,
	    		1450823800,
	    		9876543210
	    	])
	    );
	}

	/** @expectedException \InvalidArgumentException */
	public function testInvalidArgumentForDuplicates()
	{
	    $this->Finder->checkForDuplicates([
	    	1448797800,
	    	'foobar',
	    	1234567890
	    ]);
	}
}