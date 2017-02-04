<?php

namespace Runalyze\Model\Trackdata;

use PDO;

class InvalidInserterObjectForTrackdata_MockTester extends \Runalyze\Model\Entity
{
	public function properties()
    {
		return array('foo');
	}
}

class InserterTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PDO */
	protected $PDO;

	protected function setUp()
    {
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

	protected function tearDown()
    {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'trackdata`');
	}

	public function testWrongObject()
    {
        $this->setExpectedException((PHP_MAJOR_VERSION >= 7) ? 'TypeError' : '\PHPUnit_Framework_Error');

		new Inserter($this->PDO, new InvalidInserterObjectForTrackdata_MockTester);
	}

    public function testEmptyObject()
    {
        $T = new Entity(array(
            Entity::ACTIVITYID => 1
        ));

        $I = new Inserter($this->PDO, $T);
        $I->setAccountID(1);
        $I->insert();

        $data = $this->PDO->query('SELECT * FROM `'.PREFIX.'trackdata` WHERE `accountid`=1')->fetch(PDO::FETCH_ASSOC);
        $N = new Entity($data);

        foreach ([
            Entity::TIME,
            Entity::DISTANCE,
            Entity::HEARTRATE,
            Entity::CADENCE,
            Entity::POWER,
            Entity::TEMPERATURE,
            Entity::GROUNDCONTACT,
            Entity::VERTICAL_OSCILLATION,
            Entity::GROUNDCONTACT_BALANCE,
            Entity::SMO2_0,
            Entity::SMO2_1,
            Entity::THB_0,
            Entity::THB_1,
        ] as $key) {
            $this->assertNull($data[$key], 'Database value for '.$key.' must be null.');
            $this->assertTrue(is_array($N->get($key)), 'Object data for '.$key.' must be an array.');
            $this->assertEmpty($N->get($key), 'Object data for '.$key.' must be empty.');
        }

        $this->assertTrue($N->pauses()->isEmpty());
    }

	public function testSimpleInsert()
    {
		$T = new Entity(array(
			Entity::ACTIVITYID => 1,
			Entity::TIME => array(20, 40, 60),
			Entity::DISTANCE => array(0.1, 0.2, 0.3),
			Entity::HEARTRATE => array(100, 120, 130)
		));
		$T->pauses()->add(new Pause(40, 10));

		$I = new Inserter($this->PDO, $T);
		$I->setAccountID(1);
		$I->insert();

		$data = $this->PDO->query('SELECT * FROM `'.PREFIX.'trackdata` WHERE `accountid`=1')->fetch(PDO::FETCH_ASSOC);
		$N = new Entity($data);

		$this->assertEquals(1, $N->activityID());
		$this->assertEquals(array(20, 40, 60), $N->time());
		$this->assertEquals(array(0.1, 0.2, 0.3), $N->distance());
		$this->assertEquals(array(100, 120, 130), $N->heartRate());

		$this->assertFalse($N->pauses()->isEmpty());
	}
}
