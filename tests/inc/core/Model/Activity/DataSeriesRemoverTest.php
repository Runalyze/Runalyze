<?php

namespace Runalyze\Model\Activity;

use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class DataSeriesRemoverTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * @var \Runalyze\Model\Factory
	 */
	protected $Factory;

	protected $OutdoorID;
	protected $IndoorID;

	protected function setUp() {
		$this->PDO = \DB::getInstance();
		$this->Factory = new \Runalyze\Model\Factory(0);

		$this->PDO->exec('INSERT INTO `'.PREFIX.'sport` (`name`,`kcal`,`outside`,`accountid`,`power`) VALUES("",600,1,0,1)');
		$this->OutdoorID = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'sport` (`name`,`kcal`,`outside`,`accountid`,`power`) VALUES("",400,0,0,0)');
		$this->IndoorID = $this->PDO->lastInsertId();

		$this->Factory->clearCache('sport');
		\SportFactory::reInitAllSports();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
		$this->PDO->exec('TRUNCATE TABLE `'.PREFIX.'trackdata`');
		$this->PDO->exec('TRUNCATE TABLE `'.PREFIX.'route`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'sport`');

		$this->Factory->clearCache('sport');
		\Cache::clean();
	}

	/**
	 * Insert complete activity
	 * @param array $activity
	 * @param array $route
	 * @param array $trackdata
	 * @return int activity id
	 */
	protected function insert(array $activity, array $route, array $trackdata) {
		$activity[Entity::ROUTEID] = $this->insertRoute($route);
		$trackdata[Trackdata\Entity::ACTIVITYID] = $this->insertActivity($activity, $route, $trackdata);
		$this->insertTrackdata($trackdata);

		return $trackdata[Trackdata\Entity::ACTIVITYID];
	}

	/**
	 * @param array $data
	 * @param array $route
	 * @param array $trackdata
	 * @return int
	 */
	protected function insertActivity(array $data, array $route, array $trackdata) {
		$Inserter = new Inserter($this->PDO, new Entity($data));
		$Inserter->setRoute(new Route\Entity($route));
		$Inserter->setTrackdata(new Trackdata\Entity($trackdata));

		return $this->runInserter($Inserter);
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function insertRoute(array $data) {
		return $this->runInserter(new Route\Inserter($this->PDO, new Route\Entity($data)));
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function insertTrackdata(array $data) {
		$this->runInserter(new Trackdata\Inserter($this->PDO, new Trackdata\Entity($data)), false);
	}

	/**
	 * @param \Runalyze\Model\InserterWithAccountID $inserter
	 * @return int
	 */
	protected function runInserter(\Runalyze\Model\InserterWithAccountID $inserter, $return = true) {
		$inserter->setAccountID(0);
		$inserter->insert();

		if ($return) {
			return $inserter->insertedID();
		}
	}

	public function testSimpleExample() {
		$id = $this->insert(array(
			Entity::TIMESTAMP => time(),
			Entity::HR_AVG => 1
		), array(
			Route\Entity::GEOHASHES => array('u1xjhpfe7yvs', 'u1xjhzdtjx62', 'u1xjjp6nyp0b'),
			Route\Entity::ELEVATIONS_ORIGINAL => array(0, 220, 290),
			Route\Entity::ELEVATIONS_CORRECTED => array(210, 220, 230)
		), array(
			Trackdata\Entity::TIME => array(300, 600, 900),
			Trackdata\Entity::DISTANCE => array(1, 2, 3),
			Trackdata\Entity::TEMPERATURE => array(25, 30, 32),
			Trackdata\Entity::HEARTRATE => array(0, 250, 130)
		));

		$OldActivity = $this->Factory->activity($id);
		$this->assertTrue($OldActivity->trimp() > 0);

		$Remover = new DataSeriesRemover($this->PDO, 0, $OldActivity, $this->Factory);
		$Remover->removeFromRoute(Route\Entity::ELEVATIONS_ORIGINAL);
		$Remover->removeGPSpathFromRoute();
		$Remover->removeFromTrackdata(Trackdata\Entity::TEMPERATURE);
		$Remover->removeFromTrackdata(Trackdata\Entity::HEARTRATE);
		$Remover->saveChanges();

		$Activity = $this->Factory->activity($id);
		$Route = $this->Factory->route($Activity->get(Entity::ROUTEID));
		$Trackdata = $this->Factory->trackdata($id);

		$this->assertFalse($Activity->trimp() > 0);

		$this->assertFalse($Route->has(Route\Entity::GEOHASHES));
		$this->assertFalse($Route->hasOriginalElevations());
		$this->assertTrue($Route->hasCorrectedElevations());

		$this->assertTrue($Trackdata->has(Trackdata\Entity::TIME));
		$this->assertTrue($Trackdata->has(Trackdata\Entity::DISTANCE));
		$this->assertFalse($Trackdata->has(Trackdata\Entity::TEMPERATURE));
		$this->assertFalse($Trackdata->has(Trackdata\Entity::HEARTRATE));
	}

	public function testIfTrackdataWillBeDeleted() {
		$id = $this->insert(array(
			Entity::TIMESTAMP => time()
		), array(
		), array(
			Trackdata\Entity::TIME => array(60, 120, 180)
		));

		$OldActivity = $this->Factory->activity($id);

		$Remover = new DataSeriesRemover($this->PDO, 0, $OldActivity, $this->Factory);
		$Remover->removeFromTrackdata(Trackdata\Entity::TIME);
		$Remover->saveChanges();

		$Trackdata = $this->Factory->trackdata($id);

		$this->assertTrue($Trackdata->isEmpty());
	}

	public function testIfRouteWillBeDeleted() {
		$id = $this->insert(array(
			Entity::TIMESTAMP => time()
		), array(
			Route\Entity::GEOHASHES => array('u1xjhpfe7yvs', 'u1xjhzdtjx62', 'u1xjjp6nyp0b'),
			Route\Entity::ELEVATIONS_CORRECTED => array(200, 250, 200),
			Route\Entity::ELEVATION => 50,
			Route\Entity::ELEVATION_UP => 50,
			Route\Entity::ELEVATION_DOWN => 50
		), array(
		));

		$OldActivity = $this->Factory->activity($id);
		$RouteID = $OldActivity->get(Entity::ROUTEID);

		$Remover = new DataSeriesRemover($this->PDO, 0, $OldActivity, $this->Factory);
		$Remover->removeGPSpathFromRoute();
		$Remover->removeFromRoute(Route\Entity::ELEVATIONS_CORRECTED);
		$Remover->saveChanges();

		$Activity = $this->Factory->activity($id);
		$Route = $this->Factory->route($RouteID);

		$this->assertEquals(0, $Activity->get(Entity::ROUTEID));
		$this->assertNull($Activity->get(Entity::CLIMB_SCORE));
        $this->assertNull($Activity->get(Entity::PERCENTAGE_HILLY));
		$this->assertTrue($Route->isEmpty());
	}

	public function testRemovingAverageValues() {
		$id = $this->insert(array(
			Entity::TIMESTAMP => time(),
			Entity::HR_AVG => 150,
			Entity::TEMPERATURE => 18,
			Entity::SPORTID => $this->OutdoorID
		), array(
			Route\Entity::ELEVATIONS_CORRECTED => array(200, 250, 200)
		), array(
			Trackdata\Entity::TEMPERATURE => array(20, 20, 20),
			Trackdata\Entity::HEARTRATE => array(150, 170, 130)
		));

		$OldActivity = $this->Factory->activity($id);

		$Remover = new DataSeriesRemover($this->PDO, 0, $OldActivity, $this->Factory);
		$Remover->removeFromTrackdata(Trackdata\Entity::TEMPERATURE);
		$Remover->removeFromTrackdata(Trackdata\Entity::HEARTRATE);
		$Remover->saveChanges();

		$Activity = $this->Factory->activity($id);
		$this->assertEquals(18, $Activity->weather()->temperature()->value());
		$this->assertEquals(0, $Activity->hrAvg());
	}

	public function testRemovingAverageTemperature() {
		$id = $this->insert(array(
			Entity::TIMESTAMP => time(),
			Entity::TEMPERATURE => 20,
			Entity::SPORTID => $this->OutdoorID
		), array(
		), array(
			Trackdata\Entity::TEMPERATURE => array(20, 20, 20)
		));

		$OldActivity = $this->Factory->activity($id);

		$Remover = new DataSeriesRemover($this->PDO, 0, $OldActivity, $this->Factory);
		$Remover->removeFromTrackdata(Trackdata\Entity::TEMPERATURE);
		$Remover->saveChanges();

		$Activity = $this->Factory->activity($id);
		$this->assertTrue($Activity->weather()->temperature()->isUnknown());
	}

}
