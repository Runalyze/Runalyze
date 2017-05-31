<?php

namespace Runalyze\Model\Equipment;

use DB;
use PDO;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class StatisticsUpdaterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * @var int
	 */
	protected $Typeid;

	/**
	 * @var array
	 */
	protected $EquipmentIDs = array();

	protected function setUp() {
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment_type` (`name`, `accountid`) VALUES ("Test", 1)');
		$this->Typeid = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment` (`name`, `typeid`, `notes`, `distance`, `time`, `accountid`) VALUES ("Test A", '.$this->Typeid.', "", 10, 100, 1)');
		$this->EquipmentIDs[] = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment` (`name`, `typeid`, `notes`, `distance`, `time`, `accountid`) VALUES ("Test B", '.$this->Typeid.', "", 10, 100, 1)');
		$this->EquipmentIDs[] = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'equipment` (`name`, `typeid`, `notes`, `distance`, `time`, `accountid`) VALUES ("Test C", '.$this->Typeid.', "", 10, 100, 1)');
		$this->EquipmentIDs[] = $this->PDO->lastInsertId();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment_type`');
		$this->PDO->exec('DELETE FROM `'.PREFIX.'training`');
	}

	public function testStatistics() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`distance`, `s`, `accountid`, `sportid`, `time`) VALUES (10, 3600, 1, 0, 1477843525)');
		$firstActivity = $this->PDO->lastInsertId();
		$this->PDO->exec('INSERT INTO `'.PREFIX.'activity_equipment` (`activityid`, `equipmentid`) VALUES ('.$firstActivity.', '.$this->EquipmentIDs[0].')');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'activity_equipment` (`activityid`, `equipmentid`) VALUES ('.$firstActivity.', '.$this->EquipmentIDs[1].')');

		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`distance`, `s`, `accountid`,  `sportid`, `time`) VALUES (20, 7200, 1, 0, 1477843525)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'activity_equipment` (`activityid`, `equipmentid`) VALUES ('.$this->PDO->lastInsertId().', '.$this->EquipmentIDs[1].')');

		$Updater = new StatisticsUpdater($this->PDO, 1, PREFIX);
		$this->assertEquals(3, $Updater->run());

		$this->assertEquals(array(10, 3600), $this->PDO->query('SELECT `distance`, `time` FROM `'.PREFIX.'equipment` WHERE `id`='.$this->EquipmentIDs[0])->fetch(PDO::FETCH_NUM));
		$this->assertEquals(array(30, 10800), $this->PDO->query('SELECT `distance`, `time` FROM `'.PREFIX.'equipment` WHERE `id`='.$this->EquipmentIDs[1])->fetch(PDO::FETCH_NUM));
		$this->assertEquals(array(0, 0), $this->PDO->query('SELECT `distance`, `time` FROM `'.PREFIX.'equipment` WHERE `id`='.$this->EquipmentIDs[2])->fetch(PDO::FETCH_NUM));
	}

}
