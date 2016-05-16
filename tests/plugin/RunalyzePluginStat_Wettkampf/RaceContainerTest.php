<?php

namespace Runalyze\Plugin\Statistic\Races;

require_once FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wettkampf/RaceContainer.php';

use DB;
use \Runalyze\Model\RaceResult;
use \Runalyze\Model\Activity;

class RaceContainerTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var \Runalyze\Plugin\Statistic\Races\RaceContainer
	 */
	protected $Container;

	/**
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * @var int
	 */
	const SPORT_ID = 5;

	protected function setUp() {
		$this->PDO = DB::getInstance();
		$this->PDO->exec('DELETE FROM `runalyze_training`');
		$this->PDO->exec('DELETE FROM `runalyze_raceresult`');

		$this->Container = new RaceContainer(self::SPORT_ID, $this->PDO);
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `runalyze_training`');
		$this->PDO->exec('DELETE FROM `runalyze_raceresult`');
	}

	protected function insert($distance, $s = 0, $time = 0) {
		$Activity = new Activity\Entity(array(
			Activity\Entity::DISTANCE => $distance,
			Activity\Entity::TIME_IN_SECONDS => $s,
			Activity\Entity::TIMESTAMP => $time,
			Activity\Entity::SPORTID => self::SPORT_ID));
		$ActivityInserter = new Activity\Inserter($this->PDO, $Activity);
		$ActivityInserter->setAccountID(0);
		$ActivityInserter->insert();

		$RaceInserter = new RaceResult\Inserter($this->PDO, new RaceResult\Entity(array(
			RaceResult\Entity::ACTIVITY_ID => $this->PDO->lastInsertId(),
			RaceResult\Entity::OFFICIAL_DISTANCE => $distance,
			RaceResult\Entity::OFFICIAL_TIME => $s
		)));
		$RaceInserter->setAccountID(0);
		$RaceInserter->insert();
	}

	public function testGeneralFunctionality() {
		$this->insert( 1.0,  3*60 +  0);
		$this->insert( 3.0, 10*60 +  0);
		$this->insert( 3.0, 10*60 + 27);
		$this->insert( 5.0, 17*60 + 52);
		$this->insert( 5.0, 18*60 + 13);
		$this->insert( 5.1, 19*60 +  0);
		$this->insert(21.1, 1*60*60 + 19*60 +  0);

		$this->Container->fetchData();

		$this->assertEquals(7, $this->Container->num());
		$this->assertEquals(array('1', '3', '5', '5.1', '21.1'), $this->Container->distances());

		$Races1k = $this->Container->races( 1.0);
		$Races3k = $this->Container->races( 3.0);
		$Races5k = $this->Container->races( 5.0);
		$Races51 = $this->Container->races( 5.1);
		$RacesHM = $this->Container->races(21.1);

		$this->assertEquals(1, count($Races1k));
		$this->assertEquals(2, count($Races3k));
		$this->assertEquals(2, count($Races5k));
		$this->assertEquals(1, count($Races51));
		$this->assertEquals(1, count($RacesHM));

		$this->assertEquals(10*60 +  0, $Races3k[0]['s']);
		$this->assertEquals(10*60 + 27, $Races3k[1]['s']);
	}

}
