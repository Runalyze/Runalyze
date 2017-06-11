<?php

namespace Runalyze\Tests\Sports\ClimbQuantification;

use Runalyze\Sports\ClimbQuantification\FietsIndex;

class FietsIndexTest extends \PHPUnit_Framework_TestCase
{
    /** @var FietsIndex */
    protected $Fiets;

    public function setUp()
    {
        $this->Fiets = new FietsIndex();
    }

    public function testSimpleValues()
    {
        $testMatrix = [
            [1.0, 10, 0.01],
            [1.0, 50, 0.25],
            [1.0, 100, 1.00],
            [2.0, 50, 0.125],
            [2.0, 100, 0.50],
            [2.0, 150, 1.125],
            [3.0, 150, 0.75],
            [3.0, 200, 1.333],
            [3.0, 300, 3.00],
            [5.0, 100, 0.20],
            [5.0, 200, 0.80],
            [5.0, 250, 1.25],
            [10.0, 300, 0.90],
            [10.0, 500, 2.50],
            [10.0, 750, 5.625],
            [15.0, 1000, 6.666],
        ];

        foreach ($testMatrix as $testValues) {
            $this->assertEquals(
                $testValues[2],
                $this->Fiets->getScoreFor($testValues[0], $testValues[1]),
                sprintf('Score for %.2f km with %u m fails.', $testValues[0], $testValues[1]),
                0.005
            );
        }
    }

    public function testThatAltitudeAtTopIsRespected()
    {
        $this->assertEquals(
            $this->Fiets->getScoreFor(7.0, 350) + 0.0,
            $this->Fiets->getScoreFor(7.0, 350, 200)
        );

        $this->assertEquals(
            $this->Fiets->getScoreFor(7.0, 350) + 0.0,
            $this->Fiets->getScoreFor(7.0, 350, 1000)
        );

        $this->assertLessThanOrEqual(
            $this->Fiets->getScoreFor(7.0, 350) + 1.0,
            $this->Fiets->getScoreFor(7.0, 350, 2000)
        );

        $this->assertLessThanOrEqual(
            $this->Fiets->getScoreFor(7.0, 350) + 1.743,
            $this->Fiets->getScoreFor(7.0, 350, 2743)
        );
    }

    public function testThatAltitudeAtTopCantDominateScore()
    {
        $this->assertEquals(1.5, $this->Fiets->getScoreFor(1.0, 100, 5000));
    }

    public function testManuaKea()
    {
        $this->assertEquals(28.48, $this->Fiets->getScoreFor(69.2, 4182, 4213), '', 0.01);
    }

    public function testAdditiveCalculationForExactProfile()
    {
        $this->assertEquals(
            $this->Fiets->getScoreFor(10.0, 500),
            $this->Fiets->getScoreForProfile([[5.0, 0.05], [5.0, 0.05]])
        );

        $this->assertEquals(2.5, $this->Fiets->getScoreForProfile([[5.0, 0.05], [5.0, 0.05]]), '', 0.01);
        $this->assertEquals(3.33, $this->Fiets->getScoreForProfile([[2.5, 0.10], [7.5, 0.0333]]), '', 0.01);
    }
}
