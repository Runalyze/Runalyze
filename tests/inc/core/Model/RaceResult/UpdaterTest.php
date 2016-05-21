<?php

namespace Runalyze\Model\RaceResult;

use PDO;
use DB;

use Runalyze\Model\Activity;

class UpdaterTest extends \PHPUnit_Framework_TestCase
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

	public function testSimpleUpdate()
	{
		$activityId = $this->insert(array(
			Entity::OFFICIAL_DISTANCE => '10.50',
			Entity::OFFICIAL_TIME => '4000',
			Entity::PLACE_TOTAL => '10',
			Entity::PLACE_GENDER => '25',
			Entity::PLACE_AGECLASS => '4',
			Entity::PARTICIPANTS_TOTAL => '1033',
			Entity::PARTICIPANTS_GENDER => '100',
			Entity::PARTICIPANTS_AGECLASS => '15'
		));

		$RaceResult = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'raceresult` WHERE `activity_id`='.$activityId)->fetch(PDO::FETCH_ASSOC));

		$Changed = clone $RaceResult;
		$Changed->set(Entity::PLACE_TOTAL, 20);
		
		$Updater = new Updater($this->PDO, $Changed, $RaceResult);
		$Updater->setAccountID(0);
		$Updater->update();

		$Result = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'raceresult` WHERE `activity_id`='.$activityId)->fetch(PDO::FETCH_ASSOC));

		$this->assertEquals(20, $Result->placeTotal());
	}

}
