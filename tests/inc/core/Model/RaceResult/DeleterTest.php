<?php

namespace Runalyze\Model\RaceResult;

use PDO;
use DB;

use Runalyze\Model\Activity;
use Runalyze\Model\Factory;

class DeleterTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PDO */
	protected $PDO;

	protected function setUp()
	{
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'raceresult`');

		\Cache::clean();
	}

	protected function tearDown()
	{
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'raceresult`');

		\Cache::clean();
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

	public function testThatCacheIsCleared()
	{
		$activityId = $this->insert(array(
			Entity::OFFICIAL_DISTANCE => '10.50',
			Entity::OFFICIAL_TIME => 2400
		));

		$Factory = new Factory(0);
		$RaceResult = $Factory->raceResult($activityId);

		$this->assertFalse($RaceResult->isEmpty());

		$Deleter = new Deleter($this->PDO, $RaceResult);
		$Deleter->setAccountID(0);
		$Deleter->delete();

		$this->assertTrue($Factory->raceResult($activityId)->isEmpty());
	}

}
