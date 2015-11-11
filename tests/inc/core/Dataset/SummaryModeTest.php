<?php

namespace Runalyze\Dataset;

use PDO;

class SummaryModeTest extends \PHPUnit_Framework_TestCase
{

	/** @var \PDO */
	protected $PDO;

	public function setUp()
	{
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TABLE IF NOT EXISTS `testtable` (`value` float, `s` float);');
	}

	public function tearDown()
	{
		$this->PDO->exec('DROP TABLE `testtable`');
	}

	protected function fetchResult($mode)
	{
		return $this->PDO->query('SELECT '.SummaryMode::query($mode, 'value').' FROM `testtable`')->fetchColumn();
	}

	public function testNoSummary()
	{
		$this->assertEquals('', SummaryMode::query(SummaryMode::NO, 'value'));
	}

	public function testAvg()
	{
		$this->PDO->exec('INSERT INTO `testtable` VALUES (1, 1), (2, 1), (3, 1)');

		$this->assertEquals(2, $this->fetchResult(SummaryMode::AVG));
	}

	public function testAvgBasedOnDuration()
	{
		$this->PDO->exec('INSERT INTO `testtable` VALUES (10, 7), (20, 2), (30, 1), (513, 0)');

		$this->assertEquals(14, $this->fetchResult(SummaryMode::AVG));
	}

	public function testSum()
	{
		$this->PDO->exec('INSERT INTO `testtable` VALUES (1, 1), (2, 1), (3, 1)');

		$this->assertEquals(6, $this->fetchResult(SummaryMode::SUM));
	}

	public function testMax()
	{
		$this->PDO->exec('INSERT INTO `testtable` VALUES (10, 7), (20, 2), (30, 1), (513, 0)');

		$this->assertEquals(513, $this->fetchResult(SummaryMode::MAX));
	}

	public function testMin()
	{
		$this->PDO->exec('INSERT INTO `testtable` VALUES (10, 7), (20, 2), (30, 1), (513, 0)');

		$this->assertEquals(10, $this->fetchResult(SummaryMode::MIN));
	}

	public function testAvgWithoutNull()
	{
		$this->PDO->exec('INSERT INTO `testtable` VALUES (5, 1), (0, 1)');

		$this->assertEquals(5, $this->fetchResult(SummaryMode::AVG));
	}

}
