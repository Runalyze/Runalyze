<?php

namespace Runalyze\Dataset;

use DB;

class QueryTest extends \PHPUnit_Framework_TestCase
{

	protected $PDO;

	public function setUp()
	{
		$this->PDO = DB::getInstance();
	}

	public function testThatMethodsWorkForDefaultConfiguration()
	{
		$Query = new Query(new DefaultConfiguration(), $this->PDO, 1);
		$Query->fetchSummary(1);
		$Query->fetchSummaryForTimerange(1);

		$Query->showOnlyPublicActivities();
		$Query->fetchSummary(1, time() - 366*DAY_IN_S, time());
		$Query->fetchSummaryForTimerange(2, 31*DAY_IN_S);
		$Query->fetchSummaryForTimerange(3, 366*DAY_IN_S, time(), time() + 366*DAY_IN_S);
	}

	public function testThatSelectQueriesAreValid()
	{
		$Query = new Query(new DefaultConfiguration(), $this->PDO, 1);

		$this->PDO->query('SELECT '.$Query->queryToSelectAllKeys().' FROM `runalyze_training` LIMIT 1')->fetch();
		$this->PDO->query('SELECT '.$Query->queryToSelectActiveKeys().' FROM `runalyze_training` LIMIT 1')->fetch();
	}

}
