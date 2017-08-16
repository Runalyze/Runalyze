<?php

namespace Runalyze\Model\Swimdata;

use PDO;
use DB;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected $ActivityID;

	protected function setUp() {
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('INSERT INTO `runalyze_training` (`accountid`, `sportid`, `time`, `s`) VALUES (0, 0, 1477843525, 2)');

		$this->ActivityID = $this->PDO->lastInsertId();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `runalyze_training`');
	}

	public function testSimpleUpdate() {
		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(0);
		$Inserter->insert(new Entity(array(
			Entity::ACTIVITYID => $this->ActivityID,
			Entity::STROKE => array(25, 20, 15, 20)
		)));

		$Swimdata = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'swimdata` WHERE `activityid`='.$this->ActivityID)->fetch(PDO::FETCH_ASSOC));
		$Swimdata->set(Entity::STROKE, array());

		$Changed = clone $Swimdata;
		$Changed->set(Entity::STROKETYPE, array(2, 2, 2, 2));

		$Updater = new Updater($this->PDO, $Changed, $Swimdata);
		$Updater->setAccountID(0);
		$Updater->update();

		$Result = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'swimdata` WHERE `activityid`='.$this->ActivityID)->fetch(PDO::FETCH_ASSOC));
		$this->assertEquals(array(25, 20, 15, 20), $Result->stroke());
		$this->assertEquals(array(2, 2, 2, 2), $Result->stroketype());
	}

}
