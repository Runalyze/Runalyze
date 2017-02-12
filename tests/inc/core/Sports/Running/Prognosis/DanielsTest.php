<?php

namespace Runalyze\Tests\Sports\Running\Prognosis;

use Runalyze\Sports\Running\Prognosis\Daniels;

class DanielsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Daniels */
	protected $Daniels;

	protected function setUp()
    {
		$this->Daniels = new Daniels();
	}

	public function testWithoutReferenceTime()
    {
        $this->assertFalse($this->Daniels->areValuesValid());
    }

    public function testInSeconds()
    {
        $this->Daniels->adjustForBasicEndurance(false);

        $distances = array(5.0, 10.0, 21.0975, 42.195);
        $requirements = array(
            30 => array([0, 30, 40], [1,  3, 46], [2, 21,  4], [4, 49, 17]),
            35 => array([0, 27,  0], [0, 56,  3], [2,  4, 13], [4, 16, 13]),
            40 => array([0, 24,  8], [0, 50,  3], [1, 50, 59], [3, 49, 45]),
            45 => array([0, 21, 50], [0, 45, 16], [1, 40, 20], [3, 28, 26]),
            50 => array([0, 19, 57], [0, 41, 21], [1, 31, 35], [3, 10, 49]),
            55 => array([0, 18, 22], [0, 38,  6], [1, 24, 18], [2, 56,  1]),
            60 => array([0, 17,  3], [0, 35, 22], [1, 18,  9], [2, 43, 25]),
            65 => array([0, 15, 54], [0, 33,  1], [1, 12, 53], [2, 32, 35]),
            70 => array([0, 14, 55], [0, 31,  0], [1,  8, 21], [2, 23, 10]),
            75 => array([0, 14,  3], [0, 29, 14], [1,  4, 23], [2, 14, 55])
        );

        foreach ($requirements as $vdot => $times) {
            $this->Daniels->setVdot($vdot);

            foreach ($times as $i => $time) {
                $this->assertEquals(
                    $time[0] * 60 * 60 + $time[1] * 60 + $time[2],
                    $this->Daniels->getSeconds($distances[$i]),
                    'Failure for VDOT = '.$vdot.' at '.$distances[$i].' km ',
                    $distances[$i] * 1.5
                );
            }
        }
    }

    public function testGetAdjustedVdotForDistanceIfWanted()
    {
        $this->Daniels->setVdot(30.0);
        $this->Daniels->adjustForBasicEndurance(true);
        $this->Daniels->setBasicEndurance(0.0);

        $this->assertEquals(30 * 1.0, $this->Daniels->getAdjustedVdotForDistanceIfWanted(0.0));
        $this->assertEquals(30 * 0.6, $this->Daniels->getAdjustedVdotForDistanceIfWanted(50.0));

        $this->Daniels->adjustForBasicEndurance(false);

        $this->assertEquals(30.0, $this->Daniels->getAdjustedVdotForDistanceIfWanted(0.0));
        $this->assertEquals(30.0, $this->Daniels->getAdjustedVdotForDistanceIfWanted(50.0));
    }

    public function testGetAdjustedVdorForDistance()
    {
        $this->Daniels->setVdot(30.0);
        $this->Daniels->setBasicEndurance(0.0);

        $this->assertEquals(30 * 1.0, $this->Daniels->getAdjustedVdotForDistance(0.0));
        $this->assertEquals(30 * 0.6, $this->Daniels->getAdjustedVdotForDistance(50.0));

        $this->Daniels->setBasicEndurance(100.0);

        $this->assertEquals(30 * 1.0, $this->Daniels->getAdjustedVdotForDistance(40.0));
        $this->assertEquals(30 * 0.91, $this->Daniels->getAdjustedVdotForDistance(50.0), '', 0.2);

        $this->Daniels->setVDOT(60.0);
        $this->Daniels->setBasicEndurance(0.0);

        $this->assertEquals(60 * 1.0, $this->Daniels->getAdjustedVdotForDistance(0.0));
        $this->assertEquals(60 * 0.6, $this->Daniels->getAdjustedVdotForDistance(50.0));

        $this->Daniels->setBasicEndurance(100.0);

        $this->assertEquals(60 * 1.0, $this->Daniels->getAdjustedVdotForDistance(40.0));
        $this->assertEquals(60 * 0.91, $this->Daniels->getAdjustedVdotForDistance(50.0), '', 0.2);
    }

    public function testGetAdjustmentFactor()
    {
        $this->Daniels->setBasicEndurance(0.0);

        $this->assertEquals(1.00, $this->Daniels->getAdjustmentFactor(0.0), '', 0);
        $this->assertEquals(0.93, $this->Daniels->getAdjustmentFactor(10.0), '', 0.01);
        $this->assertEquals(0.84, $this->Daniels->getAdjustmentFactor(20.0), '', 0.01);
        $this->assertEquals(0.625, $this->Daniels->getAdjustmentFactor(40.0), '', 0.01);
        $this->assertEquals(0.60, $this->Daniels->getAdjustmentFactor(50.0), '', 0.01);
        $this->assertEquals(0.60, $this->Daniels->getAdjustmentFactor(100.0), '', 0.01);

        $this->Daniels->setBasicEndurance(50.0);

        $this->assertEquals(1.00, $this->Daniels->getAdjustmentFactor(0.0), '', 0);
        $this->assertEquals(1.00, $this->Daniels->getAdjustmentFactor(10.0), '', 0.01);
        $this->assertEquals(1.00, $this->Daniels->getAdjustmentFactor(20.0), '', 0.01);
        $this->assertEquals(0.825, $this->Daniels->getAdjustmentFactor(40.0), '', 0.01);
        $this->assertEquals(0.71, $this->Daniels->getAdjustmentFactor(50.0), '', 0.01);
        $this->assertEquals(0.60, $this->Daniels->getAdjustmentFactor(100.0), '', 0.01);

        $this->Daniels->setBasicEndurance(100.0);

        $this->assertEquals(1.00, $this->Daniels->getAdjustmentFactor(40.0), '', 0.01);
        $this->assertEquals(0.91, $this->Daniels->getAdjustmentFactor(50.0), '', 0.01);
        $this->assertEquals(0.60, $this->Daniels->getAdjustmentFactor(100.0), '', 0.01);

        $this->Daniels->setBasicEndurance(200.0);

        $this->assertEquals(1.00, $this->Daniels->getAdjustmentFactor(50.0), '', 0.01);
        $this->assertEquals(0.65, $this->Daniels->getAdjustmentFactor(100.0), '', 0.01);

        $this->Daniels->setBasicEndurance(300.0);

        $this->assertEquals(1.00, $this->Daniels->getAdjustmentFactor(100.0), '', 0.01);
    }
}
