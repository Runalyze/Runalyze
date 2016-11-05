<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Tool\DatabaseCleanup;

use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobLoop;
use Runalyze\Configuration;

class JobLoopTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PDO */
	protected $PDO;

	protected function setUp()
    {
		$this->PDO = \DB::getInstance();
		$this->PDO->exec('TRUNCATE TABLE `runalyze_route`');
		$this->PDO->exec('TRUNCATE TABLE `runalyze_trackdata`');
		$this->PDO->exec('DELETE FROM `runalyze_training`');
	}

	protected function tearDown()
    {
		$this->PDO->exec('TRUNCATE TABLE `runalyze_route`');
		$this->PDO->exec('TRUNCATE TABLE `runalyze_trackdata`');
		$this->PDO->exec('DELETE FROM `runalyze_training`');
	}

	public function testNoLoopForSingleActivity()
    {
		$this->PDO->exec(
			'INSERT INTO `runalyze_training` (`id`, `distance`, `s`, `pulse_avg`, `sportid`, `accountid`) '.
			'VALUES (1, 10, 3600, 150, '.Configuration::General()->runningSport().', 0)'
		);

		$Loop = new JobLoop([], $this->PDO, 0, 'runalyze_');
		$Loop->run();

		$data = $this->PDO->query('SELECT * FROM `runalyze_training` WHERE `id`=1 LIMIT 1')->fetch();
		$this->assertEquals(0, $data['elevation']);
		$this->assertEquals(0, $data['vdot']);
		$this->assertEquals(0, $data['vdot_by_time']);
		$this->assertEquals(0, $data['vdot_with_elevation']);
		$this->assertEquals(0, $data['jd_intensity']);
		$this->assertEquals(0, $data['trimp']);
	}

	public function testCompleteLoopForSingleActivity()
    {
		$this->PDO->exec(
			'INSERT INTO `runalyze_training` (`id`, `distance`, `s`, `pulse_avg`, `sportid`, `accountid`) '.
			'VALUES (1, 10, 3600, 150, '.Configuration::General()->runningSport().', 0)'
		);

		$Loop = new JobLoop([
            JobLoop::ELEVATION => true,
            JobLoop::ELEVATION_OVERWRITE => true,
            JobLoop::VDOT => true,
            JobLoop::JD_POINTS => true,
            JobLoop::TRIMP => true
        ], $this->PDO, 0, 'runalyze_');
		$Loop->run();

		$data = $this->PDO->query('SELECT * FROM `runalyze_training` WHERE `id`=1 LIMIT 1')->fetch();
		$this->assertNotEquals(0, $data['vdot']);
		$this->assertNotEquals(0, $data['vdot_by_time']);
		$this->assertNotEquals(0, $data['vdot_with_elevation']);
		$this->assertNotEquals(0, $data['jd_intensity']);
		$this->assertNotEquals(0, $data['trimp']);
	}

	public function testOverwriteElevation()
	{
		$this->PDO->exec('INSERT INTO `runalyze_training` (`id`, `distance`, `s`, `elevation`, `routeid`, `accountid`) VALUES (1, 10, 3600, 42, 1, 0)');
		$this->PDO->exec('INSERT INTO `runalyze_route` (`id`, `elevation`, `elevation_up`, `elevation_down`, `accountid`) VALUES (1, 123, 123, 123, 0)');

		$Loop = new JobLoop([
			JobLoop::ELEVATION => true,
			JobLoop::ELEVATION_OVERWRITE => true
		], $this->PDO, 0, 'runalyze_');
		$Loop->run();

		$this->assertEquals(123, $this->PDO->query(
			'SELECT `elevation` FROM `runalyze_training` WHERE `id`=1 LIMIT 1'
		)->fetchColumn());
	}

	public function testDontOverwriteElevation()
    {
		$this->PDO->exec('INSERT INTO `runalyze_training` (`id`, `distance`, `s`, `elevation`, `routeid`, `accountid`) VALUES (1, 10, 3600, 42, 1, 0)');
		$this->PDO->exec('INSERT INTO `runalyze_route` (`id`, `elevation`, `elevation_up`, `elevation_down`, `accountid`) VALUES (1, 123, 123, 123, 0)');

		$Loop = new JobLoop([
            JobLoop::ELEVATION => true,
        ], $this->PDO, 0, 'runalyze_');
		$Loop->run();

		$this->assertEquals(42, $this->PDO->query(
			'SELECT `elevation` FROM `runalyze_training` WHERE `id`=1 LIMIT 1'
		)->fetchColumn());
	}

	public function testUsageOfCorrectElevation()
    {
		$this->PDO->exec(
			'INSERT INTO `runalyze_training` (`id`, `routeid`, `distance`, `s`, `pulse_avg`, `sportid`, `accountid`) '.
			'VALUES (1, 2, 10, 3600, 150, '.Configuration::General()->runningSport().', 0)'
		);
		$this->PDO->exec(
			'INSERT INTO `runalyze_training` (`id`, `routeid`, `distance`, `s`, `pulse_avg`, `sportid`, `accountid`) '.
			'VALUES (2, 1, 10, 3600, 150, '.Configuration::General()->runningSport().', 0)'
		);
		$this->PDO->exec('INSERT INTO `runalyze_route` (`id`, `elevations_corrected`, `accountid`) VALUES (1, "0|100", 0)');
		$this->PDO->exec('INSERT INTO `runalyze_route` (`id`, `elevations_corrected`, `accountid`) VALUES (2, "200|0", 0)');

		$Loop = new JobLoop([
            JobLoop::ELEVATION => true,
            JobLoop::ELEVATION_OVERWRITE => true,
            JobLoop::VDOT => true
        ], $this->PDO, 0, 'runalyze_');
		$Loop->run();

		$DataDown = $this->PDO->query('SELECT `elevation`, `vdot`, `vdot_with_elevation` FROM `runalyze_training` WHERE `id`=1 LIMIT 1')->fetch();
		$DataUp = $this->PDO->query('SELECT `elevation`, `vdot`, `vdot_with_elevation` FROM `runalyze_training` WHERE `id`=2 LIMIT 1')->fetch();

		$this->assertEquals($DataUp['vdot'], $DataDown['vdot']);
		$this->assertEquals(100, $DataUp['elevation']);
		$this->assertEquals(200, $DataDown['elevation']);
		$this->assertGreaterThan($DataDown['vdot_with_elevation'], $DataUp['vdot_with_elevation']);
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1970
	 */
	public function testUsageOfCorrectElevationForVdot()
	{
		$this->PDO->exec(
			'INSERT INTO `runalyze_training` (`id`, `routeid`, `distance`, `s`, `pulse_avg`, `sportid`, `accountid`) '.
			'VALUES (1, 2, 10, 3600, 150, '.Configuration::General()->runningSport().', 0)'
		);
		$this->PDO->exec(
			'INSERT INTO `runalyze_training` (`id`, `routeid`, `distance`, `s`, `pulse_avg`, `sportid`, `accountid`) '.
			'VALUES (2, 1, 10, 3600, 150, '.Configuration::General()->runningSport().', 0)'
		);
		$this->PDO->exec('INSERT INTO `runalyze_route` (`id`, `elevation`, `elevation_up`, `elevation_down`, `accountid`) VALUES (1, 200, 200, 0, 0)');
		$this->PDO->exec('INSERT INTO `runalyze_route` (`id`, `elevation`, `elevation_up`, `elevation_down`, `accountid`) VALUES (2, 200, 0, 200, 0)');

		$Loop = new JobLoop([
			JobLoop::VDOT => true
		], $this->PDO, 0, 'runalyze_');
		$Loop->run();

		$vdotElevationDown = $this->PDO->query('SELECT `vdot_with_elevation` FROM `runalyze_training` WHERE `id`=1 LIMIT 1')->fetchColumn();
		$vdotElevationUp = $this->PDO->query('SELECT `vdot_with_elevation` FROM `runalyze_training` WHERE `id`=2 LIMIT 1')->fetchColumn();

		$this->assertGreaterThan($vdotElevationDown, $vdotElevationUp);
	}

	public function testIgnoreVDOTforNotRunning()
    {
		$this->PDO->exec(
			'INSERT INTO `runalyze_training` (`id`, `distance`, `s`, `pulse_avg`, `sportid`, `accountid`) '.
			'VALUES (1, 10, 3600, 150, '.(Configuration::General()->runningSport() + 1).', 0)'
		);

		$Loop = new JobLoop([
            JobLoop::VDOT => true,
            JobLoop::JD_POINTS => true,
            JobLoop::TRIMP => true
        ], $this->PDO, 0, 'runalyze_');
		$Loop->run();

		$data = $this->PDO->query('SELECT * FROM `runalyze_training` WHERE `id`=1 LIMIT 1')->fetch();
		$this->assertEquals(0, $data['vdot']);
		$this->assertEquals(0, $data['vdot_by_time']);
		$this->assertEquals(0, $data['vdot_with_elevation']);
		$this->assertEquals(0, $data['jd_intensity']);
		$this->assertNotEquals(0, $data['trimp']);
	}
}
