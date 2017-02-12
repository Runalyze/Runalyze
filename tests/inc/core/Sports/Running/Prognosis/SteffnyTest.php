<?php

namespace Runalyze\Tests\Sports\Running\Prognosis;

use Runalyze\Sports\Running\Prognosis\Steffny;

class SteffnyTest extends \PHPUnit_Framework_TestCase
{
    /** @var Steffny */
	protected $Steffny;

	protected function setUp()
    {
		$this->Steffny = new Steffny();
	}

	public function testWithoutReferenceTime()
    {
        $this->assertFalse($this->Steffny->areValuesValid());
    }

	public function testSetReferenceFrom10kTime()
    {
		$this->Steffny->setReferenceFrom10kTime(41 * 60 + 0);

		$this->assertTrue($this->Steffny->areValuesValid());

		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0));
		$this->assertEquals(20 * 60 + 0, $this->Steffny->getSeconds(5.0));
	}

	public function testSetReferenceResult()
    {
		$this->Steffny->setReferenceResult(9.9, 40 * 60 + 35);
		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0), '', 1);

		$this->Steffny->setReferenceResult(5.1, 20 * 60 + 24);
		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0));

		$this->Steffny->setReferenceResult(5.0, 20 * 60 + 0);
		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0));

		$this->Steffny->setReferenceResult(3.0, 11 * 60 + 40);
		$this->assertEquals(11 * 60 + 40, $this->Steffny->getSeconds(3.0), '', 1);
		$this->assertEquals(20 * 60 + 0, $this->Steffny->getSeconds(5.0), '', 1);
		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0), '', 1);

		$this->Steffny->setReferenceResult(1.5, 5 * 60 + 40);
		$this->assertEquals(5 * 60 + 40, $this->Steffny->getSeconds(1.5), '', 1);
		$this->assertEquals(11 * 60 + 40, $this->Steffny->getSeconds(3.0), '', 1);
		$this->assertEquals(20 * 60 + 0, $this->Steffny->getSeconds(5.0), '', 1);
		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0), '', 1);


		$this->Steffny->setReferenceResult(10.1, 41 * 60 + 25);
		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0), '', 1);

		$this->Steffny->setReferenceResult(21.0, 90 * 60 + 11);
		$this->assertEquals(90 * 60 + 36, $this->Steffny->getSeconds(21.0975), '', 1);
		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0), '', 1);

		$this->Steffny->setReferenceResult(21.0975, 90 * 60 + 36);
		$this->assertEquals(90 * 60 + 36, $this->Steffny->getSeconds(21.0975), '', 1);
		$this->assertEquals(41 * 60 + 0, $this->Steffny->getSeconds(10.0), '', 1);
	}

	public function testInSeconds()
    {
		$this->Steffny->setReferenceResult(1.5, 4 * 60 + 30);

		$this->assertEquals(  4 * 60 + 30, $this->Steffny->getSeconds(1.5), '', 1);
		$this->assertEquals(  9 * 60 + 20, $this->Steffny->getSeconds(3.0), '' , 1);
		$this->assertEquals( 16 * 60 +  6, $this->Steffny->getSeconds(5.0), '', 1);
		$this->assertEquals( 33 * 60 + 12, $this->Steffny->getSeconds(10.0), '', 1);
		$this->assertEquals( 73 * 60 + 22, $this->Steffny->getSeconds(21.0975), '', 2);
		$this->assertEquals(154 * 60 + 49, $this->Steffny->getSeconds(42.195), '', 5);
		$this->assertEquals(464 * 60 + 27 - 25 * 60 - 11, $this->Steffny->getSeconds(100.0), '', 20);
	}

	/**
	 * Test halfmarathon table, see page 148.
	 */
	public function testHalfmarathonTable()
    {
		$Requirements = array(
			array([62, 30], [2, 18,  0]),
			array([60,  0], [2, 12, 30]),
			array([57, 30], [2,  7,  0]),
			array([55,  0], [2,  1, 30]),
			array([52, 30], [1, 56,  0]),
			array([50,  0], [1, 50, 30]),
			array([47, 30], [1, 45,  0]),
			array([45,  0], [1, 39, 30]),
			array([42, 30], [1, 34,  0]),
			array([40,  0], [1, 28, 30]),
			array([37, 30], [1, 23,  0]),
			array([35,  0], [1, 17, 30]),
			array([32, 30], [1, 12,  0]),
			array([30,  0], [1,  6, 30]),
			array([27, 30], [1,  1,  0]),
			array([26, 30], [0, 58, 34])
		);

		foreach ($Requirements as $Requirement) {
			$this->Steffny->setReferenceFrom10kTime($Requirement[0][0] * 60.0 + $Requirement[0][1]);
			$this->assertEquals(
				$Requirement[1][0] * 3600.0 + $Requirement[1][1] * 60.0 + $Requirement[1][2],
				$this->Steffny->getSeconds(21.0975),
				'Prediction failed for 10k in '.($Requirement[0][0]).':'.$Requirement[0][1],
				15
			);
		}
	}

	/**
	 * Test marathon table, see page 183.
	 */
	public function testMarathonTable()
    {
		$Requirements = array(
			array([62, 30], [4, 55,  0]),
			array([60,  0], [4, 40,  0]),
			array([57, 30], [4, 28, 20]),
			array([55,  0], [4, 16, 40]),
			array([52, 30], [4,  5,  0]),
			array([50,  0], [3, 53, 20]),
			array([47, 30], [3, 41, 40]),
			array([45,  0], [3, 30,  0]),
			array([42, 30], [3, 18, 20]),
			array([40,  0], [3,  6, 40]),
			array([37, 30], [2, 55,  0]),
			array([35,  0], [2, 43, 20]),
			array([32, 30], [2, 31, 40]),
			array([30,  0], [2, 20,  0]),
			array([27, 30], [2,  8, 20]),
			array([26, 30], [2,  3, 39])
		);

		foreach ($Requirements as $Requirement) {
			$this->Steffny->setReferenceFrom10kTime($Requirement[0][0] * 60.0 + $Requirement[0][1]);

			$this->assertEquals(
				$Requirement[1][0] * 3600.0 + $Requirement[1][1] * 60.0 + $Requirement[1][2],
				$this->Steffny->getSeconds(42.195),
				'Prediction failed for 10k in '.($Requirement[0][0]).':'.$Requirement[0][1],
				5 * 60
			);
		}
	}
}
