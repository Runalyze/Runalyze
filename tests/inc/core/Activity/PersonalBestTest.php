<?php

namespace Runalyze\Activity;

use Runalyze\Configuration;

use DB;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class PersonalBestTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;
	
	/**
	 * @var int
	 */
	const SPORTID = 5;

	protected function setUp() {
		PersonalBest::deactivateStaticCache();

		$this->PDO = DB::getInstance();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
	}

	protected function insert($dist, $s, $time = 0) {
		$this->PDO->exec(
			'INSERT INTO `'.PREFIX.'training` (`distance`, `s`, `time`, `sportid`, `accountid`) '.
			'VALUES ('.$dist.', '.$s.', '.$time.', '.self::SPORTID.', 0)'
		);
		$activityId = $this->PDO->lastInsertId();
		$this->PDO->exec(
			'INSERT INTO `'.PREFIX.'raceresult` (`official_distance`, `official_time`, `accountid`, `activity_id`) '.
			'VALUES ('.$dist.', '.$s.', 0, '.$activityId.')'
		);
		return $activityId;
	}

	public function testAutoLookup() {
		$this->insert(1, 200);

		$AutoLookup = new PersonalBest(1, self::SPORTID, $this->PDO, true);
		$NoLookup = new PersonalBest(1, self::SPORTID, $this->PDO, false);

		$this->assertTrue($AutoLookup->exists());
		$this->assertFalse($NoLookup->exists());

		$this->insert(1, 180);
		$this->assertEquals(200, $AutoLookup->seconds());
		$this->assertEquals(180, $NoLookup->lookup()->seconds());
	}

	public function testStaticCache() {
		$this->insert(1, 200);

		PersonalBest::activateStaticCache();

		$PB = new PersonalBest(1, self::SPORTID, $this->PDO, true);
		$this->assertEquals(200, $PB->seconds());

		$this->insert(1, 180);
		$this->assertEquals(200, $PB->lookup()->seconds());

		PersonalBest::deactivateStaticCache();

		$this->assertEquals(180, $PB->lookup()->seconds());
	}

	public function testDetails() {
		$this->insert(1, 200, mktime(0,0,0,1,1,2010));

		$PB = new PersonalBest(1, self::SPORTID, $this->PDO, false);
		$PB->lookupWithDetails();

		$this->assertTrue($PB->knowsActivity());
		$this->assertGreaterThan(0, $PB->activityId());
		$this->assertEquals(mktime(0,0,0,1,1,2010), $PB->timestamp());
	}

	public function testMultiLookup() {
		PersonalBest::activateStaticCache();

		$this->insert(1.0,  180);
		$this->insert(1.0,  200);
		$this->insert(3.10,  600);
		$this->insert(3.10,  650);
		$this->insert(5.0, 1100);
		$this->insert(5.0, 1200);

		$this->assertEquals(3, PersonalBest::lookupDistances(array(1, 3.1, 5), self::SPORTID, $this->PDO));

		$PDO = new \PDO('sqlite::memory:');

		$PB1k = new PersonalBest(1, self::SPORTID, $PDO, true);
		$PB3k = new PersonalBest("3.1", self::SPORTID, $PDO, true);
		$PB5k = new PersonalBest(5, self::SPORTID, $PDO, true);

		$this->assertEquals( 180, $PB1k->seconds());
		$this->assertEquals( 600, $PB3k->seconds());
		$this->assertEquals(1100, $PB5k->seconds());
	}

	public function testMultiLookupWithDetails() {
		PersonalBest::activateStaticCache();
		$date = mktime(12,0,0,6,1,2010);

		$this->insert(1.0,  180, $date);
		$this->insert(1.0,  200, $date);
		$this->insert(3.10,  600, $date);
		$this->insert(3.10,  650, $date);
		$this->insert(5.0, 1100, $date);
		$this->insert(5.0, 1200, $date);

		$this->assertEquals(3, PersonalBest::lookupDistances(array(1, 3.1, 5), self::SPORTID, $this->PDO, true));

		$PDO = new \PDO('sqlite::memory:');

		$PB1k = new PersonalBest(1, self::SPORTID, $PDO, true, true);
		$PB3k = new PersonalBest("3.1", self::SPORTID, $PDO, true, true);
		$PB5k = new PersonalBest(5, self::SPORTID, $PDO, true);
		$PB5k->lookupWithDetails();

		$this->assertEquals(  180, $PB1k->seconds());
		$this->assertEquals($date, $PB1k->timestamp());
		$this->assertEquals(  600, $PB3k->seconds());
		$this->assertEquals($date, $PB3k->timestamp());
		$this->assertEquals( 1100, $PB5k->seconds());
		$this->assertEquals($date, $PB5k->timestamp());
	}

	public function testMultiLookupWithDetailsForIdenticalResults() {
		PersonalBest::activateStaticCache();
		$date1 = mktime(12,0,0,6,1,2010);
		$date2 = mktime(12,0,0,6,2,2010);

		$first = $this->insert(1.0,  180, $date1);
		$second = $this->insert(1.0,  180, $date2);

		$this->assertEquals(1, PersonalBest::lookupDistances(array(1.0), self::SPORTID, $this->PDO, true));

		$PDO = new \PDO('sqlite::memory:');

		$PB = new PersonalBest(1, self::SPORTID, $PDO, true, true);

		$this->assertEquals(180, $PB->seconds());
		$this->assertEquals($date1, $PB->timestamp());
		$this->assertEquals($first, $PB->activityId());
	}

}
