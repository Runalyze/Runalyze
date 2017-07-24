<?php

namespace Runalyze\Model\Trackdata;

use PDO;

/**
 * @group requiresSqlite
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TABLE IF NOT EXISTS `'.PREFIX.'trackdata` (
			`accountid` int(10),
			`activityid` int(10),
			`time` longtext,
			`distance` longtext,
			`heartrate` longtext,
			`cadence` longtext,
			`power` longtext,
			`temperature` longtext,
			`groundcontact` longtext,
			`vertical_oscillation` longtext,
			`groundcontact_balance` longtext,
			`smo2_0` longtext,
            `smo2_1` longtext,
            `thb_0` longtext,
            `thb_1` longtext,
			`pauses` text,
			PRIMARY KEY (`activityid`)
			);
		');
	}

	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'trackdata`');
	}

	public function testSimpleUpdate() {
		$Inserter = new Inserter($this->PDO, new Entity(array(
			Entity::ACTIVITYID => 1,
			Entity::TIME => array(20, 40, 60),
			Entity::DISTANCE => array(0.1, 0.2, 0.3),
			Entity::HEARTRATE => array(100, 120, 130)
		)));
		$Inserter->setAccountID(1);
		$Inserter->insert();

		$Track = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'trackdata` WHERE `activityid`=1')->fetch(PDO::FETCH_ASSOC));
		$Track->set(Entity::HEARTRATE, array(120, 140, 150));

		$Changed = clone $Track;
		$Changed->set(Entity::DISTANCE, array(0.15, 0.3, 0.45));

		$Updater = new Updater($this->PDO, $Changed, $Track);
		$Updater->setAccountID(1);
		$Updater->update();

		$Result = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'trackdata` WHERE `activityid`=1')->fetch(PDO::FETCH_ASSOC));

		$this->assertEquals(1, $Result->activityID());
		$this->assertEquals(array(20, 40, 60), $Result->time());
		$this->assertEquals(array(0.15, 0.3, 0.45), $Result->distance());
		$this->assertEquals(array(100, 120, 130), $Result->heartRate());
	}

}
