<?php

namespace Runalyze\Model\RaceResult;

use PDO;
use DB;

use Runalyze\Model\Activity;

class InserterTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PDO */
	protected $PDO;

	protected function setUp()
	{
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'raceresult`');
	}

	protected function tearDown()
	{
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'raceresult`');
	}

	/**
	 * @param array $data
	 * @return int inserted activity id (is primary key for race result)
	 */
	protected function insert(array $data)
	{
		$ActivityInserter = new Activity\Inserter($this->PDO);
		$ActivityInserter->setAccountID(0);
		$ActivityInserter->insert(new Activity\Entity(array()));
		$activityId = $ActivityInserter->insertedID();

		$RaceResult = new Entity($data);
		$RaceResult->set(Entity::ACTIVITY_ID, $activityId);

		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(0);
		$Inserter->insert($RaceResult);

		return $activityId;
	}

	public function testSimpleInsert()
	{
		$activityId = $this->insert(array(
			Entity::OFFICIAL_DISTANCE => '10.50',
			Entity::OFFICIAL_TIME => 2400,
			Entity::OFFICIALLY_MEASURED => '1',
			Entity::PLACE_TOTAL => '10',
			Entity::PLACE_GENDER => '25',
			Entity::PLACE_AGECLASS => '4',
			Entity::PARTICIPANTS_TOTAL => '1033',
			Entity::PARTICIPANTS_GENDER => '100',
			Entity::PARTICIPANTS_AGECLASS => '15'
		));

		$data = $this->PDO->query('SELECT * FROM `'.PREFIX.'raceresult` WHERE `activity_id`='.$activityId)->fetch(PDO::FETCH_ASSOC);
		$RaceResult = new Entity($data);

		$this->assertEquals(10.50, $RaceResult->officialDistance());
		$this->assertEquals(2400, $RaceResult->officialTime());
		$this->assertTrue($RaceResult->officiallyMeasured());
		$this->assertEquals(10, $RaceResult->placeTotal());
		$this->assertEquals(25, $RaceResult->placeGender());
		$this->assertEquals(4, $RaceResult->placeAgeclass());
		$this->assertEquals(1033, $RaceResult->participantsTotal());
		$this->assertEquals(100, $RaceResult->participantsGender());
		$this->assertEquals(15, $RaceResult->participantsAgeclass());
	}

}
