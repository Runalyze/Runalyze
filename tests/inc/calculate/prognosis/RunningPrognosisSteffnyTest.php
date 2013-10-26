<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-10-26 at 21:31:09.
 */
class RunningPrognosisSteffnyTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var RunningPrognosisSteffny
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new RunningPrognosisSteffny;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers RunningPrognosisSteffny::setupFromDatabase
	 * @todo   Implement testSetupFromDatabase().
	 */
	public function testSetupFromDatabase() {
		// TODO
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers RunningPrognosisSteffny::setReferenceFrom10kTime
	 */
	public function testSetReferenceFrom10kTime() {
		$this->object->setReferenceFrom10kTime(41 * 60 + 0);

		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10) );
		$this->assertEquals( 20*60 + 0, $this->object->inSeconds(5) );
	}

	/**
	 * @covers RunningPrognosisSteffny::setReferenceResult
	 */
	public function testSetReferenceResult() {
		$this->object->setReferenceResult(9.9, 40*60 + 35);
		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10), '', 1 );

		$this->object->setReferenceResult(5.1, 20*60 + 24);
		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10) );

		$this->object->setReferenceResult(5, 20*60 + 0);
		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10) );

		$this->object->setReferenceResult(3, 11*60 + 40);
		$this->assertEquals( 11*60 + 40, $this->object->inSeconds(3), '', 1 );
		$this->assertEquals( 20*60 + 0, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10), '', 1 );

		$this->object->setReferenceResult(1.5, 5*60 + 40);
		$this->assertEquals( 5*60 + 40, $this->object->inSeconds(1.5), '', 1 );
		$this->assertEquals( 11*60 + 40, $this->object->inSeconds(3), '', 1 );
		$this->assertEquals( 20*60 + 0, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10), '', 1 );


		$this->object->setReferenceResult(10.1, 41*60 + 25);
		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10), '', 1 );

		$this->object->setReferenceResult(21.0, 90*60 + 11);
		$this->assertEquals( 90*60 + 36, $this->object->inSeconds(21.0975), '', 1 );
		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10), '', 1 );

		$this->object->setReferenceResult(21.0975, 90*60 + 36);
		$this->assertEquals( 90*60 + 36, $this->object->inSeconds(21.0975), '', 1 );
		$this->assertEquals( 41*60 + 0, $this->object->inSeconds(10), '', 1 );
	}

	/**
	 * @covers RunningPrognosisSteffny::setReferenceResult
	 * @covers RunningPrognosisSteffny::inSeconds
	 */
	public function testInSeconds() {
		$this->object->setReferenceResult( 1.5, 4*60 + 30);

		$this->assertEquals(   4*60 + 30, $this->object->inSeconds(1.5), '', 1 );
		$this->assertEquals(   9*60 + 20, $this->object->inSeconds(3), '' , 1);
		$this->assertEquals(  16*60 +  6, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals(  33*60 + 12, $this->object->inSeconds(10), '', 1 );
		$this->assertEquals(  73*60 + 22, $this->object->inSeconds(21.0975), '', 2 );
		$this->assertEquals( 154*60 + 49, $this->object->inSeconds(42.195), '', 5 );
		$this->assertEquals( 464*60 + 27 - 25*60 - 11, $this->object->inSeconds(100), '', 20 );
	}

	/**
	 * Test halfmarathon table, see page 148.
	 */
	public function testHalfmarathonTable() {
		$Requirements = array(
			array( array(62,30), array(2,18, 0) ),
			array( array(60, 0), array(2,12,30) ),
			array( array(57,30), array(2, 7, 0) ),
			array( array(55, 0), array(2, 1,30) ),
			array( array(52,30), array(1,56, 0) ),
			array( array(50, 0), array(1,50,30) ),
			array( array(47,30), array(1,45, 0) ),
			array( array(45, 0), array(1,39,30) ),
			array( array(42,30), array(1,34, 0) ),
			array( array(40, 0), array(1,28,30) ),
			array( array(37,30), array(1,23, 0) ),
			array( array(35, 0), array(1,17,30) ),
			array( array(32,30), array(1,12, 0) ),
			array( array(30, 0), array(1, 6,30) ),
			array( array(27,30), array(1, 1, 0) ),
			array( array(26,30), array(0,58,34) )
		);

		foreach ($Requirements as $Requirement) {
			$this->object->setReferenceFrom10kTime($Requirement[0][0]*60 + $Requirement[0][1]);
			$this->assertEquals(
				$Requirement[1][0]*60*60 + $Requirement[1][1]*60 + $Requirement[1][2],
				$this->object->inSeconds(21.0975),
				'Prediction failed for 10k in '.($Requirement[0][0]).':'.$Requirement[0][1],
				15
			);
		}
	}

	/**
	 * Test halfmarathon table, see page 183.
	 */
	public function testMarathonTable() {
		$Requirements = array(
			array( array(62,30), array(4,55, 0) ),
			array( array(60, 0), array(4,40, 0) ),
			array( array(57,30), array(4,28,20) ),
			array( array(55, 0), array(4,16,40) ),
			array( array(52,30), array(4, 5, 0) ),
			array( array(50, 0), array(3,53,20) ),
			array( array(47,30), array(3,41,40) ),
			array( array(45, 0), array(3,30, 0) ),
			array( array(42,30), array(3,18,20) ),
			array( array(40, 0), array(3, 6,40) ),
			array( array(37,30), array(2,55, 0) ),
			array( array(35, 0), array(2,43,20) ),
			array( array(32,30), array(2,31,40) ),
			array( array(30, 0), array(2,20, 0) ),
			array( array(27,30), array(2, 8,20) ),
			array( array(26,30), array(2, 3,39) )
		);

		foreach ($Requirements as $Requirement) {
			$this->object->setReferenceFrom10kTime($Requirement[0][0]*60 + $Requirement[0][1]);
			$this->assertEquals(
				$Requirement[1][0]*60*60 + $Requirement[1][1]*60 + $Requirement[1][2],
				$this->object->inSeconds(42.195),
				'Prediction failed for 10k in '.($Requirement[0][0]).':'.$Requirement[0][1],
				5*60
			);
		}
	}

}
