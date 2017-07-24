<?php

namespace Runalyze\Model\Route;

use PDO;
use DB;

class InvalidInserterObjectForRoute_MockTester extends \Runalyze\Model\Entity
{
	public function properties()
    {
		return array('foo');
	}
}

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class InserterTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PDO */
	protected $PDO;

	protected function setUp()
    {
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	protected function tearDown()
    {
		$this->PDO->exec('TRUNCATE `'.PREFIX.'route`');
	}

	public function testWrongObject()
    {
	    $this->setExpectedException((PHP_MAJOR_VERSION >= 7) ? 'TypeError' : '\PHPUnit_Framework_Error');

		new Inserter($this->PDO, new InvalidInserterObjectForRoute_MockTester);
	}

	public function testSimpleInsert()
    {
		$R = new Entity(array(
			Entity::NAME => 'Test route',
			Entity::DISTANCE => 3.14,
			Entity::GEOHASHES => array('u1xjhpfe7yvs', 'u1xjhzdtjx62')
		));
		$R->forceToSetMinMaxFromGeohashes();

		$I = new Inserter($this->PDO, $R);
		$I->setAccountID(0);
		$I->insert();

		$data = $this->PDO->query('SELECT * FROM `'.PREFIX.'route` WHERE `accountid`=0')->fetch(PDO::FETCH_ASSOC);
		$N = new Entity($data);

		$this->assertEquals(0, $data[Inserter::ACCOUNTID]);
		$this->assertEquals('Test route', $N->name());
		$this->assertTrue($N->hasID());
		$this->assertTrue($N->hasPositionData());
		$this->assertEquals('u1xjhpdt5z', $N->get(Entity::MIN));

        $this->assertNull($data[Entity::ELEVATIONS_ORIGINAL]);
        $this->assertEmpty($N->elevationsOriginal());
	}

	public function testElevationCalculation()
    {
		$R = new Entity(array(
			Entity::ELEVATIONS_CORRECTED => array(100, 120, 110)
		));

		$I = new Inserter($this->PDO, $R);
		$I->setAccountID(0);
		$I->insert();

		$data = $this->PDO->query('SELECT * FROM `'.PREFIX.'route` WHERE `accountid`=0')->fetch(PDO::FETCH_ASSOC);
		$N = new Entity($data);

		$this->assertGreaterThan(0, $N->elevation());
		$this->assertGreaterThan(0, $N->elevationUp());
		$this->assertGreaterThan(0, $N->elevationDown());

		$this->assertNotNull($R->get(Entity::ELEVATIONS_ORIGINAL));
        $this->assertNotNull($R->get(Entity::GEOHASHES));

        $this->assertNull($data[Entity::ELEVATIONS_ORIGINAL]);
        $this->assertNull($data[Entity::GEOHASHES]);

        $this->assertNull($data[Entity::STARTPOINT]);
        $this->assertNull($data[Entity::ENDPOINT]);
        $this->assertNull($data[Entity::MIN]);
        $this->assertNull($data[Entity::MAX]);
	}
}
