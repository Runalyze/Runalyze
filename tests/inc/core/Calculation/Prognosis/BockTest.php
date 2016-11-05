<?php

namespace Runalyze\Calculation\Prognosis;

use DB;
use Runalyze\Configuration;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-10-26 at 23:45:59.
 */
class BockTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Runalyze\Calculation\Prognosis\Bock
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new Bock();
	}

	public function testSetupFromDatabase() {
		DB::getInstance()->insert('training', array('sportid', 'vdot_by_time', 's', 'distance', 'time'), array(Configuration::General()->runningSport(), 90, 7*60 + 30, 3, 1477839906) );
		DB::getInstance()->insert('training', array('sportid', 'vdot_by_time', 's', 'distance', 'time'), array(Configuration::General()->runningSport(), 60, 16*60 + 32, 5, 1477839906) );
		DB::getInstance()->insert('training', array('sportid', 'vdot_by_time', 's', 'distance', 'time'), array(Configuration::General()->runningSport(), 70, 76*60 + 14, 21.1, 1477839906) );

		$this->object->setMinimalDistance(4);
		$this->object->setupFromDatabase();

		$this->assertEquals(   9*60 + 36, $this->object->inSeconds(3), '', 1 );
		$this->assertEquals(  16*60 + 32, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals(  34*60 + 30, $this->object->inSeconds(10), '', 1 );
		$this->assertEquals(  76*60 + 14, $this->object->inSeconds(21.1), '', 1 );
		$this->assertEquals( 159*60 +  7, $this->object->inSeconds(42.2), '', 1 );

		DB::getInstance()->exec('DELETE FROM `runalyze_training`');
	}

	public function testSetFromResultsAndInSeconds() {
		// Remember: Formulas used in Bock's generator do not match to his tables
		$this->object->setFromResults(10, 30*60 + 0, 21.1, 65*60);
		$this->assertEquals(   8*60 + 37, $this->object->inSeconds(3), '', 1 );
		$this->assertEquals(  14*60 + 37, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals(  30*60 +  0, $this->object->inSeconds(10), '', 1 );
		$this->assertEquals(  65*60 +  0, $this->object->inSeconds(21.1), '', 1 );
		$this->assertEquals( 133*60 + 14, $this->object->inSeconds(42.2), '', 1 );

		$this->object->setFromResults(10, 30*60 + 0, 21.1, 70*60);
		$this->assertEquals(   7*60 + 39, $this->object->inSeconds(3), '', 1 );
		$this->assertEquals(  13*60 + 40, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals(  30*60 +  0, $this->object->inSeconds(10), '', 1 );
		$this->assertEquals(  70*60 +  0, $this->object->inSeconds(21.1), '', 1 );
		$this->assertEquals( 153*60 + 42, $this->object->inSeconds(42.2), '', 1 );

		$this->object->setFromResults(10, 40*60 + 0, 21.1, 90*60);
		$this->assertEquals(  10*60 + 49, $this->object->inSeconds(3), '', 1 );
		$this->assertEquals(  18*60 + 51, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals(  40*60 +  0, $this->object->inSeconds(10), '', 1 );
		$this->assertEquals(  90*60 +  0, $this->object->inSeconds(21.1), '', 1 );
		$this->assertEquals( 191*60 +  4, $this->object->inSeconds(42.2), '', 1 );
	}

}
