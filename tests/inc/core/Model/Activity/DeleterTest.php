<?php

namespace Runalyze\Model\Activity;

use Runalyze\Configuration;
use Runalyze\Model;

use PDO;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class DeleterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected $EquipmentType;
	protected $EquipmentA;
	protected $EquipmentB;

	protected function setUp() {
		\Cache::clean();

		$this->PDO = \DB::getInstance();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'sport` (`name`,`id`,`kcal`,`outside`,`accountid`) VALUES("",1,600,1,0)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'sport` (`name`,`id`,`kcal`,`outside`,`accountid`) VALUES("",2,400,0,0)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment_type` (`name`,`accountid`) VALUES("Type",0)');
		$this->EquipmentType = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment_sport` (`sportid`,`equipment_typeid`) VALUES(1,'.$this->EquipmentType.')');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment` (`name`,`typeid`,`notes`,`accountid`) VALUES("A",'.$this->EquipmentType.',"",0)');
		$this->EquipmentA = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment` (`name`,`typeid`,`notes`,`accountid`) VALUES("B",'.$this->EquipmentType.',"",0)');
		$this->EquipmentB = $this->PDO->lastInsertId();

		$this->PDO->exec('DELETE FROM `'.PREFIX.'raceresult`');
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'trackdata`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'swimdata`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'route`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'sport`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'raceresult`');

		\Cache::clean();
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function insert(array $data) {
		$Inserter = new Inserter($this->PDO, new Entity($data));
		$Inserter->setAccountID(0);
		$Inserter->insert();

		return $Inserter->insertedID();
	}

	/**
	 * @param int $id
	 */
	protected function delete($id) {
		$Deleter = new Deleter($this->PDO, $this->fetch($id));
		$Deleter->setAccountID(0);
		$Deleter->delete();
	}

	/**
	 * @param int $id
	 * @return \Runalyze\Model\Activity\Entity
	 */
	protected function fetch($id) {
		return new Entity(
			$this->PDO->query('SELECT * FROM `'.PREFIX.'training` WHERE `id`="'.$id.'" AND `accountid`=0')->fetch(PDO::FETCH_ASSOC)
		);
	}

	public function testWrongObject() {
	    if (PHP_MAJOR_VERSION >= 7) $this->setExpectedException('TypeError'); else $this->setExpectedException('\PHPUnit_Framework_Error');
		new Deleter($this->PDO, new Model\Trackdata\Entity);
	}

	public function testStartTimeUpdate() {
		$current = time();
		$old = mktime(0,0,0,1,1,2006);
		$older = mktime(0,0,0,1,1,2003);
		$oldest = mktime(0,0,0,1,1,2000);

		Configuration::Data()->updateStartTime($current);

		$this->insert(array(Entity::TIMESTAMP => $current));
		$oldId = $this->insert(array(Entity::TIMESTAMP => $old));
		$olderId = $this->insert(array(Entity::TIMESTAMP => $older));
		$oldestId = $this->insert(array(Entity::TIMESTAMP => $oldest));

		$this->assertEquals($oldest, Configuration::Data()->startTime());

		$this->delete($olderId);
		$this->assertEquals($oldest, Configuration::Data()->startTime());

		$this->delete($oldestId);
		$this->assertEquals($old, Configuration::Data()->startTime());

		$this->delete($oldId);
		$this->assertEquals($current, Configuration::Data()->startTime());
	}

	public function testVO2maxShapeForChanges() {
		$activityData = array(
			Entity::TIMESTAMP => time(),
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 30*60,
			Entity::HR_AVG => 150,
			Entity::SPORTID => Configuration::General()->runningSport(),
			Entity::USE_VO2MAX => true
		);

		$trainingId = $this->insert($activityData);
		$raceId = $this->insert($activityData);

		$RaceInserter = new Model\RaceResult\Inserter($this->PDO, new Model\RaceResult\Entity(array(
			Model\RaceResult\Entity::OFFICIAL_DISTANCE => '10',
			Model\RaceResult\Entity::OFFICIAL_TIME => 30*60,
			Model\RaceResult\Entity::ACTIVITY_ID => $raceId
		)));
		$RaceInserter->setAccountID(0);
		$RaceInserter->insert();

		Configuration::Data()->updateVO2maxCorrector(0.85);

		$this->assertEquals(0.85, Configuration::Data()->vo2maxCorrector());
		$this->assertNotEquals(0, Configuration::Data()->vo2maxShape());

		$this->delete($trainingId);

		$this->assertEquals(0.85, Configuration::Data()->vo2maxCorrector());
		$this->assertNotEquals(0, Configuration::Data()->vo2maxShape());

		$this->delete($raceId);

		$this->assertNotEquals(0.85, Configuration::Data()->vo2maxCorrector());
		$this->assertEquals(0, Configuration::Data()->vo2maxShape());
	}

	public function testVO2maxStatisticsForNoChanges() {
		$IDs = array();
		$IDs[] = $this->insert(array(
			Entity::TIMESTAMP => time(),
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 30*60,
			Entity::HR_AVG => 150,
			Entity::SPORTID => Configuration::General()->runningSport() + 1,
			Entity::USE_VO2MAX => true
		));
		$IDs[] = $this->insert(array(
			Entity::TIMESTAMP => time(),
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 30*60,
			Entity::HR_AVG => 150,
			Entity::SPORTID => Configuration::General()->runningSport(),
			Entity::USE_VO2MAX => false
		));
		$IDs[] = $this->insert(array(
			Entity::TIMESTAMP => time(),
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 30*60,
			Entity::SPORTID => Configuration::General()->runningSport(),
			Entity::USE_VO2MAX => true
		));
		$IDs[] = $this->insert(array(
			Entity::TIMESTAMP => time() - 365*DAY_IN_S,
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 30*60,
			Entity::HR_AVG => 150,
			Entity::SPORTID => Configuration::General()->runningSport(),
			Entity::USE_VO2MAX => true
		));

		Configuration::Data()->updateVO2maxShape(62.15);
		Configuration::Data()->updateVO2maxCorrector(0.85);

		foreach ($IDs as $id) {
			$this->delete($id);
		}

		$this->assertEquals(62.15, Configuration::Data()->vo2maxShape());
		$this->assertEquals(0.85, Configuration::Data()->vo2maxCorrector());
	}

	public function testUpdatingBasicEndurance() {
		$ignoredId1 = $this->insert(array(
			Entity::TIMESTAMP => time() - 365*DAY_IN_S,
			Entity::DISTANCE => 30,
			Entity::TIME_IN_SECONDS => 30*60*3,
			Entity::SPORTID => Configuration::General()->runningSport()
		));
		$ignoredId2 = $this->insert(array(
			Entity::TIMESTAMP => time(),
			Entity::DISTANCE => 30,
			Entity::TIME_IN_SECONDS => 30*60*3,
			Entity::SPORTID => Configuration::General()->runningSport() + 1
		));
		$relevantId = $this->insert(array(
			Entity::TIMESTAMP => time(),
			Entity::DISTANCE => 30,
			Entity::TIME_IN_SECONDS => 30*60*3,
			Entity::SPORTID => Configuration::General()->runningSport()
		));

		$this->assertNotEquals(0, Configuration::Data()->basicEndurance());

		$this->delete($ignoredId1);
		$this->assertNotEquals(0, Configuration::Data()->basicEndurance());

		$this->delete($ignoredId2);
		$this->assertNotEquals(0, Configuration::Data()->basicEndurance());

		$this->delete($relevantId);
		$this->assertEquals(0, Configuration::Data()->basicEndurance());
	}

	public function testDeletionOfRoute() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'route` (`accountid`) VALUES(0)');
		$routeID = $this->PDO->lastInsertId();

		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`routeid`,`accountid`, `sportid`, `time`, `s`) VALUES('.$routeID.',0, 0, 1477843525, 2)');
		$activityID = $this->PDO->lastInsertId();

		$this->delete($activityID);

		$this->assertEquals(array(), $this->PDO->query('SELECT `id` FROM `'.PREFIX.'route` WHERE `id`='.$routeID)->fetchAll());
		$this->assertEquals(array(), $this->PDO->query('SELECT `id` FROM `'.PREFIX.'training` WHERE `id`='.$activityID)->fetchAll());
	}

	public function testDeletionOfTrackdata() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`accountid`, `sportid`, `time`, `s`) VALUES(0, 0, 1477843525, 2)');
		$activityID = $this->PDO->lastInsertId();

		$this->PDO->exec('INSERT INTO `'.PREFIX.'trackdata` (`activityid`, `accountid`) VALUES('.$activityID.', 0)');

		$this->delete($activityID);

		$this->assertEquals(array(), $this->PDO->query('SELECT `activityid` FROM `'.PREFIX.'trackdata` WHERE `activityid`='.$activityID)->fetchAll());
		$this->assertEquals(array(), $this->PDO->query('SELECT `id` FROM `'.PREFIX.'training` WHERE `id`='.$activityID)->fetchAll());
	}

	public function testDeletionOfSwimdata() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`accountid`, `sportid`, `time`, `s`) VALUES(0, 0, 1477843525, 2)');
		$activityID = $this->PDO->lastInsertId();

		$this->PDO->exec('INSERT INTO `'.PREFIX.'swimdata` (`activityid`, `accountid`) VALUES('.$activityID.', 0)');

		$this->delete($activityID);

		$this->assertEquals(array(), $this->PDO->query('SELECT `activityid` FROM `'.PREFIX.'swimdata` WHERE `activityid`='.$activityID)->fetchAll());
		$this->assertEquals(array(), $this->PDO->query('SELECT `id` FROM `'.PREFIX.'training` WHERE `id`='.$activityID)->fetchAll());
	}

	public function testEquipment() {
		$this->PDO->exec('UPDATE `runalyze_equipment` SET `distance`=0, `time`=0 WHERE `id`='.$this->EquipmentA);
		$this->PDO->exec('UPDATE `runalyze_equipment` SET `distance`=0, `time`=0 WHERE `id`='.$this->EquipmentB);

		$Object = new Entity(array(
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 3600,
			Entity::SPORTID => 1
		));
		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(0);
		$Inserter->setEquipmentIDs(array($this->EquipmentA));
		$Inserter->insert($Object);

		$this->assertEquals(array(10, 3600), $this->PDO->query('SELECT `distance`, `time` FROM `runalyze_equipment` WHERE `id`='.$this->EquipmentA)->fetch(PDO::FETCH_NUM));
		$this->assertEquals(array( 0,    0), $this->PDO->query('SELECT `distance`, `time` FROM `runalyze_equipment` WHERE `id`='.$this->EquipmentB)->fetch(PDO::FETCH_NUM));

		$Deleter = new Deleter($this->PDO, $Object);
		$Deleter->setAccountID(0);
		$Deleter->setEquipmentIDs(array($this->EquipmentA));
		$Deleter->delete();

		$this->assertEquals(array(0, 0), $this->PDO->query('SELECT `distance`, `time` FROM `runalyze_equipment` WHERE `id`='.$this->EquipmentA)->fetch(PDO::FETCH_NUM));
		$this->assertEquals(array(0, 0), $this->PDO->query('SELECT `distance`, `time` FROM `runalyze_equipment` WHERE `id`='.$this->EquipmentB)->fetch(PDO::FETCH_NUM));
	}

}
