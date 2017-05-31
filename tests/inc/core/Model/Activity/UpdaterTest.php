<?php

namespace Runalyze\Model\Activity;

use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geohash\Geohash;
use Runalyze\Configuration;
use Runalyze\Data\Weather\Condition;
use Runalyze\Model;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\Util\LocalTime;

use PDO;
use Shoe;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected $OutdoorID;
	protected $IndoorID;

	protected $EquipmentType;
	protected $EquipmentA;
	protected $EquipmentB;

	protected function setUp() {
		\Cache::clean();
		$this->PDO = \DB::getInstance();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'sport` (`name`,`kcal`,`outside`,`accountid`,`power`,`HFavg`) VALUES("",600,1,0,1,160)');
		$this->OutdoorID = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'sport` (`name`,`kcal`,`outside`,`accountid`,`power`,`HFavg`) VALUES("",400,0,0,0,100)');
		$this->IndoorID = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'sport` (`name`,`kcal`,`outside`,`accountid`,`power`) VALUES("Running",400,0,0,0)');
		$this->runningSportId = $this->PDO->lastInsertId();
		$this->PDO->exec("INSERT INTO runalyze_conf (`category`, `key`, `value`, `accountid`) VALUES ('general', 'RUNNINGSPORT', ".$this->runningSportId.", 0)");
		Configuration::loadAll(0);
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment_type` (`name`,`accountid`) VALUES("Type",0)');
		$this->EquipmentType = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment_sport` (`sportid`,`equipment_typeid`) VALUES('.$this->OutdoorID.','.$this->EquipmentType.')');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment` (`name`,`typeid`,`notes`,`accountid`) VALUES("A",'.$this->EquipmentType.',"",0)');
		$this->EquipmentA = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment` (`name`,`typeid`,`notes`,`accountid`) VALUES("B",'.$this->EquipmentType.',"",0)');
		$this->EquipmentB = $this->PDO->lastInsertId();

		$Factory = new Model\Factory(0);
		$Factory->clearCache('sport');
		\SportFactory::reInitAllSports();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'sport`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment_type`');
		$this->PDO->exec('DELETE FROM runalyze_conf');
		Configuration::loadAll(0);
		$Factory = new Model\Factory(0);
		$Factory->clearCache('sport');
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
	 * @param \Runalyze\Model\Activity\Entity $new
	 * @param \Runalyze\Model\Activity\Entity $old [optional]
	 * @param \Runalyze\Model\Trackdata\Entity $track [optional]
	 * @param \Runalyze\Model\Route\Entity $route [optional]
	 * @return \Runalyze\Model\Activity\Entity
	 */
	protected function update(Entity $new, Entity $old = null, Model\Trackdata\Entity $track = null, Model\Route\Entity $route = null, $force = false) {
		$Updater = new Updater($this->PDO, $new, $old);
		$Updater->setAccountID(0);

		if (null !== $track) {
			$Updater->setTrackdata($track);
		}
		if (null !== $route) {
			$Updater->setRoute($route);
		}

		$Updater->forceRecalculations($force);
		$Updater->update();

		return $this->fetch($new->id());
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
		new Updater($this->PDO, new Model\Trackdata\Entity);
	}

	public function testSimpleUpdate() {
		$OldObject = $this->fetch( $this->insert(array(
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 3000
		)) );

		$NewObject = clone $OldObject;
		$NewObject->set(Entity::TIME_IN_SECONDS, 3600);

		$Result = $this->update($NewObject, $OldObject);

		$this->assertEquals(10, $Result->distance());
		$this->assertEquals(3600, $Result->duration());
		$this->assertGreaterThan(time()-10, $Result->get(Entity::TIMESTAMP_EDITED));
	}

	public function testWithCalculationsFromAdditionalObjects() {
		$OldObject = $this->fetch( $this->insert(array(
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 3000,
			Entity::HR_AVG => 150,
			Entity::SPORTID => Configuration::General()->runningSport()
		)) );

		$NewObject = clone $OldObject;

		$Result = $this->update($NewObject, $OldObject, new Model\Trackdata\Entity(array(
			Model\Trackdata\Entity::TIME => array(1500, 3000),
			Model\Trackdata\Entity::HEARTRATE => array(125, 175)
		)), new Model\Route\Entity(array(
			Model\Route\Entity::ELEVATION_UP => 500,
			Model\Route\Entity::ELEVATION_DOWN => 100
		)), true);

		$this->assertEquals($OldObject->vo2maxByTime(), $Result->vo2maxByTime());
		$this->assertEquals($OldObject->vo2maxByHeartRate(), $Result->vo2maxByHeartRate());
		$this->assertGreaterThan($OldObject->vo2maxWithElevation(), $Result->vo2maxWithElevation());
		$this->assertGreaterThan($OldObject->trimp(), $Result->trimp());
	}

	public function testTrimpCalculations() {
		$OldObject = $this->fetch( $this->insert(array(
			Entity::TIME_IN_SECONDS => 3000,
			Entity::SPORTID => $this->IndoorID
		)) );

		$NewObject = clone $OldObject;
		$NewObject->set(Entity::SPORTID, $this->OutdoorID);

		$Result = $this->update($NewObject, $OldObject);

		$this->assertGreaterThan($OldObject->trimp(), $Result->trimp());
	}

	public function testUnsettingRunningValues() {
		$OldObject = $this->fetch( $this->insert(array(
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 3000,
			Entity::HR_AVG => 150,
			Entity::SPORTID => Configuration::General()->runningSport()
		)) );

		$this->assertGreaterThan(0, $OldObject->vo2maxByTime());
		$this->assertGreaterThan(0, $OldObject->vo2maxByHeartRate());
		$this->assertGreaterThan(0, $OldObject->vo2maxWithElevation());
		$this->assertGreaterThan(0, $OldObject->trimp());

		$NewObject = clone $OldObject;
		$NewObject->set(Entity::SPORTID, $NewObject->sportid() + 1);

		$Result = $this->update($NewObject, $OldObject);

		$this->assertEquals(0, $Result->vo2maxByTime());
		$this->assertEquals(0, $Result->vo2maxByHeartRate());
		$this->assertEquals(0, $Result->vo2maxWithElevation());
		$this->assertGreaterThan(0, $Result->trimp());
	}

	public function testVO2maxStatisticsUpdate() {
		$current = time();
		$timeago = mktime(0,0,0,1,1,2000);
		$running = Configuration::General()->runningSport();
		Configuration::Data()->updateVO2maxShape(0);
		Configuration::Data()->updateVO2maxCorrector(1);

		$Object1 = $this->fetch( $this->insert(array(
			Entity::TIMESTAMP => $timeago,
			Entity::DISTANCE => 10,
			Entity::TIME_IN_SECONDS => 30*60,
			Entity::HR_AVG => 150,
			Entity::SPORTID => $running,
			Entity::USE_VO2MAX => true
		)) );

		$this->assertEquals(0, Configuration::Data()->vo2maxShape());
		$this->assertEquals(1, Configuration::Data()->vo2maxCorrectionFactor());

		$Object2 = clone $Object1;
		$Object2->set(Entity::TIMESTAMP, $current);
		$this->update($Object2, $Object1);

		$this->assertNotEquals(0, Configuration::Data()->vo2maxShape());
		$this->assertEquals(1, Configuration::Data()->vo2maxCorrectionFactor());

		$Object3 = clone $Object2;
		$Object3->set(Entity::TIMESTAMP, $timeago);
		$this->update($Object3, $Object2);

		$this->assertEquals(0, Configuration::Data()->vo2maxShape());
		$this->assertEquals(1, Configuration::Data()->vo2maxCorrectionFactor());
	}

	public function testStartTimeUpdate() {
		$current = time();
		$timeago = mktime(0,0,0,1,1,2000);

		Configuration::Data()->updateStartTime($current);

		$OldObject = $this->fetch( $this->insert(array(
			Entity::TIMESTAMP => $current
		)) );

		$NewObject = clone $OldObject;
		$NewObject->set(Entity::TIMESTAMP, $current);
		$this->update($NewObject, $OldObject);

		$this->assertEquals($current, Configuration::Data()->startTime());

		$NewObject->set(Entity::TIMESTAMP, $timeago);
		$this->update($NewObject, $OldObject);

		$this->assertEquals($timeago, Configuration::Data()->startTime());

		$this->insert(array(
			Entity::TIMESTAMP => $timeago + 100
		));
		$this->assertEquals($timeago, Configuration::Data()->startTime());

		$NewestObject = clone $NewObject;
		$NewestObject->set(Entity::TIMESTAMP, $current);
		$this->update($NewestObject, $NewObject);

		$this->assertEquals($timeago + 100, Configuration::Data()->startTime());
	}

	public function testUpdateTemperature() {
		$OldObject = $this->fetch( $this->insert(array(
			Entity::TEMPERATURE => 5,
			Entity::SPORTID => $this->OutdoorID
		)) );

		$this->assertFalse($OldObject->weather()->temperature()->isUnknown());

		$NewObject = clone $OldObject;
		$NewObject->weather()->temperature()->setTemperature(null);
		$Result = $this->update($NewObject, $OldObject);

		$this->assertTrue($Result->weather()->temperature()->isUnknown());
	}

	public function testUpdateTemperatureFromNullToZero() {
		$OldObject = $this->fetch( $this->insert(array(
			Entity::TEMPERATURE => null,
			Entity::SPORTID => $this->OutdoorID
		)) );

		$this->assertTrue($OldObject->weather()->temperature()->isUnknown());

		$NewObject = clone $OldObject;
		$NewObject->weather()->temperature()->setTemperature(0);
		$Result = $this->update($NewObject, $OldObject);

		$this->assertFalse($Result->weather()->temperature()->isUnknown());
	}

	public function testUpdateTemperatureWithoutOldObject() {
		$OldObject = $this->fetch( $this->insert(array(
			Entity::TEMPERATURE => 5,
			Entity::SPORTID => $this->OutdoorID
		)) );

		$this->assertFalse($OldObject->weather()->temperature()->isUnknown());

		$NewObject = clone $OldObject;
		$NewObject->weather()->temperature()->setTemperature(null);
		$Result = $this->update($NewObject);

		$this->assertTrue($Result->weather()->temperature()->isUnknown());
	}

	public function testUnsettingWeatherForInside() {
		$OldObject = $this->fetch( $this->insert(array(
			Entity::TIME_IN_SECONDS => 3600,
			Entity::WEATHERID => WeatherConditionProfile::SUNNY,
			Entity::TEMPERATURE => 7,
			Entity::HUMIDITY => 67,
			Entity::PRESSURE => 1020,
			Entity::WINDDEG => 180,
			Entity::WINDSPEED => 12,
			Entity::SPORTID => $this->OutdoorID
		)) );

		$this->assertFalse($OldObject->weather()->isEmpty());

		$NewObject = clone $OldObject;
		$NewObject->set(Entity::SPORTID, $this->IndoorID);
		$Result = $this->update($NewObject, $OldObject);

		$this->assertTrue($Result->weather()->isEmpty());
	}

	public function testUpdatePowerCalculation() {
		// TODO: Needs configuration setting
		if (Configuration::ActivityForm()->computePower()) {
			$OldObject = $this->fetch( $this->insert(array(
				Entity::DISTANCE => 10,
				Entity::TIME_IN_SECONDS => 3000,
				Entity::SPORTID => $this->IndoorID
			)));

			$NewObject = clone $OldObject;
			$NewObject->set(Entity::SPORTID, $this->OutdoorID);

			$Result = $this->update($NewObject, $OldObject, new Model\Trackdata\Entity(array(
				Model\Trackdata\Entity::TIME => array(1500, 3000),
				Model\Trackdata\Entity::DISTANCE => array(5, 10)
			)));

			$this->assertEquals(0, $OldObject->power());
			$this->assertNotEquals(0, $NewObject->power());
			$this->assertNotEquals(0, $Result->power());
		}
	}

	public function testEquipment() {
		$this->PDO->exec('UPDATE `runalyze_equipment` SET `distance`=0, `time`=0 WHERE `id`='.$this->EquipmentA);
		$this->PDO->exec('UPDATE `runalyze_equipment` SET `distance`=0, `time`=0 WHERE `id`='.$this->EquipmentB);

		$OldObject = new Entity(array(
			Entity::DISTANCE => 12.34,
			Entity::TIME_IN_SECONDS => 3600,
			Entity::SPORTID => $this->OutdoorID
		));
		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(0);
		$Inserter->setEquipmentIDs(array($this->EquipmentA));
		$Inserter->insert($OldObject);

		$this->assertEquals(array(12.34, 3600), $this->PDO->query('SELECT `distance`, `time` FROM `runalyze_equipment` WHERE `id`='.$this->EquipmentA)->fetch(PDO::FETCH_NUM));
		$this->assertEquals(array( 0.00,    0), $this->PDO->query('SELECT `distance`, `time` FROM `runalyze_equipment` WHERE `id`='.$this->EquipmentB)->fetch(PDO::FETCH_NUM));

		$NewObject = clone $OldObject;
		$Updater = new Updater($this->PDO, $NewObject, $OldObject);
		$Updater->setAccountID(0);
		$Updater->setEquipmentIDs(array($this->EquipmentB), array($this->EquipmentA));
		$Updater->update();

		$this->assertEquals(array( 0.00,    0), $this->PDO->query('SELECT `distance`, `time` FROM `runalyze_equipment` WHERE `id`='.$this->EquipmentA)->fetch(PDO::FETCH_NUM));
		$this->assertEquals(array(12.34, 3600), $this->PDO->query('SELECT `distance`, `time` FROM `runalyze_equipment` WHERE `id`='.$this->EquipmentB)->fetch(PDO::FETCH_NUM));
	}

	public function testUpdatingNight() {
		$OldObject = new Entity([
			Entity::TIMESTAMP => LocalTime::fromString('2016-01-13 08:00:00')->getTimestamp()
		]);

		$Route = new Model\Route\Entity([
			Model\Route\Entity::GEOHASHES => [(new Geohash())->encode(new Coordinate([49.44, 7.45]))->getGeohash()]
		]);
		$Route->synchronize();

		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(0);
		$Inserter->setRoute($Route);
		$Inserter->insert($OldObject);

		$Result = $this->fetch($Inserter->insertedID());
		$this->assertTrue($Result->knowsIfItIsNight());
		$this->assertTrue($Result->isNight());

		$NewObject = clone $OldObject;
		$NewObject->set(Entity::TIMESTAMP, $OldObject->timestamp() + 3600);

		$Updater = new Updater($this->PDO, $NewObject, $OldObject);
		$Updater->setAccountID(0);
		$Updater->setRoute($Route);
		$Updater->update();

		$UpdatedResult = $this->fetch($Inserter->insertedID());
		$this->assertTrue($UpdatedResult->knowsIfItIsNight());
		$this->assertFalse($UpdatedResult->isNight());
	}

}
