<?php

namespace Runalyze\Dataset;

use DB;

class QueryTest extends \PHPUnit_Framework_TestCase
{

	/** @var \PDO */
	protected $PDO;

	/** @var \Runalyze\Dataset\Query */
	protected $Query;

	/** @var int */
	protected $Running;

	public function setUp()
	{
		$this->PDO = DB::getInstance();
		$this->Query = new Query(new DefaultConfiguration(), $this->PDO, 1);
		$this->Running = \Runalyze\Configuration::General()->runningSport();
	}

	public function tearDown()
	{
		$this->PDO->exec('DELETE FROM `runalyze_training`');
	}

	protected function insertActivity(array $data)
	{
		if (!isset($data['accountid'])) {
			$data['accountid'] = 1;
		}

		if (!isset($data['sportid'])) {
			$data['sportid'] = 1;
		}

		if (!isset($data['is_public'])) {
			$data['is_public'] = 1;
		}

		$this->PDO->query('INSERT INTO `runalyze_training` (`'.implode('`, `', array_keys($data)).'`) VALUES ("'.implode('", "', $data).'")');
	}

	public function testThatMethodsWorkForDefaultConfiguration()
	{
		$this->insertActivity(array('time' => time() - DAY_IN_S, 's' => 100));

		$this->assertInternalType('array', $this->Query->statementToFetchActivities(0, time())->fetchAll());
		$this->assertInternalType('array', $this->Query->fetchSummaryForSport($this->Running));
		$this->assertInternalType('array', $this->Query->fetchSummaryForTimerange($this->Running));

		$this->Query->showOnlyPublicActivities();
		$this->assertInternalType('array', $this->Query->fetchSummaryForSport($this->Running, time() - 366*DAY_IN_S, time()));
		$this->assertInternalType('array', $this->Query->fetchSummaryForTimerange($this->Running, 31*DAY_IN_S));
		$this->assertInternalType('array', $this->Query->fetchSummaryForTimerange($this->Running, 366*DAY_IN_S, time() - 2*DAY_IN_S, time() + 366*DAY_IN_S));
	}

	public function testThatSelectQueriesAreValid()
	{
		$this->PDO->query('SELECT '.$this->Query->queryToSelectAllKeys().' FROM `runalyze_training` LIMIT 1')->fetch();
		$this->PDO->query('SELECT '.$this->Query->queryToSelectActiveKeys().' FROM `runalyze_training` LIMIT 1')->fetch();
	}

	public function testTimerangeAndPrivacyForSingleActivities()
	{
		$this->insertActivity(array('time' => time() - DAY_IN_S, 'is_public' => '0', 's' => 600));
		$this->insertActivity(array('time' => time() - DAY_IN_S, 'is_public' => '1', 's' => 300));
		$this->insertActivity(array('time' => time() + DAY_IN_S, 'is_public' => '1', 's' => 100));

		$this->Query->showOnlyPublicActivities();
		$allActivities = $this->Query->statementToFetchActivities(0, time())->fetchAll();

		$this->assertEquals(1, count($allActivities));
		$this->assertEquals(300, $allActivities[0]['s']);
	}

	public function testDurationSumWithDistance()
	{
		$this->insertActivity(array('time' => time() - DAY_IN_S, 's' => 900, 'distance' => 0));
		$this->insertActivity(array('time' => time() - DAY_IN_S, 's' => 600, 'distance' => 2));

		$Context = new Context($this->Query->fetchSummaryForSport($this->Running), 1);

		$this->assertEquals(600, $Context->data(Keys\Pace::DURATION_SUM_WITH_DISTANCE_KEY));
	}

	public function testSummary()
	{
		$this->insertActivity(array('time' => time() - 1*DAY_IN_S, 's' => 100, 'sportid' => 1));
		$this->insertActivity(array('time' => time() - 5*DAY_IN_S, 's' => 200, 'sportid' => 2));
		$this->insertActivity(array('time' => time() - 5*DAY_IN_S, 's' => 123, 'sportid' => 2));
		$this->insertActivity(array('time' => time() - 9*DAY_IN_S, 's' => 500, 'sportid' => 3));

		$summary = array(
			$this->Query->fetchSummaryForSport(1),
			$this->Query->fetchSummaryForSport(2),
			$this->Query->fetchSummaryForSport(3)
		);

		$this->assertEquals(array(1, 2, 1), array_map(function ($data) { return $data['num']; }, $summary));
		$this->assertEquals(array(100, 323, 500), array_map(function ($data) { return $data['s']; }, $summary));
	}

	public function testSummaryForWeekTimerange()
	{
		$this->insertActivity(array('time' => time() - 1*DAY_IN_S, 's' => 100));
		$this->insertActivity(array('time' => time() - 5*DAY_IN_S, 's' => 200));
		$this->insertActivity(array('time' => time() - 5*DAY_IN_S, 's' => 123, 'sportid' => $this->Running + 1));
		$this->insertActivity(array('time' => time() - 9*DAY_IN_S, 's' => 500));

		$summary = $this->Query->fetchSummaryForTimerange($this->Running);

		$this->assertEquals(2, count($summary));
		$this->assertEquals(300, $summary[0]['s']);
		$this->assertEquals(500, $summary[1]['s']);
	}

	public function testSummaryForMonthTimerange()
	{
		$this->insertActivity(array('time' => mktime(12, 0, 0, 6, 15, 2015), 's' => 100));
		$this->insertActivity(array('time' => mktime(12, 0, 0, 6, 1, 2015), 's' => 200));
		$this->insertActivity(array('time' => mktime(12, 0, 0, 6, 12, 2015), 's' => 123, 'sportid' => $this->Running + 1));
		$this->insertActivity(array('time' => mktime(12, 0, 0, 5, 31, 2015), 's' => 500));

		$summary = $this->Query->fetchSummaryForTimerange($this->Running, Query::MONTH_TIMERANGE);

		$this->assertEquals(2, count($summary));
		$this->assertEquals(300, $summary[0]['s']);
		$this->assertEquals(500, $summary[1]['s']);
	}

	public function testSummaryForYearTimerange()
	{
		$this->insertActivity(array('time' => mktime(12, 0, 0, 6, 15, 2015), 's' => 100));
		$this->insertActivity(array('time' => mktime(12, 0, 0, 1, 1, 2015), 's' => 200));
		$this->insertActivity(array('time' => mktime(12, 0, 0, 12, 31, 2014), 's' => 123, 'sportid' => $this->Running + 1));
		$this->insertActivity(array('time' => mktime(12, 0, 0, 12, 31, 2014), 's' => 500));

		$summary = $this->Query->fetchSummaryForTimerange($this->Running, Query::YEAR_TIMERANGE);

		$this->assertEquals(2, count($summary));
		$this->assertEquals(300, $summary[0]['s']);
		$this->assertEquals(500, $summary[1]['s']);
	}

}
