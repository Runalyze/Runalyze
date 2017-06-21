<?php

namespace Runalyze\Tests\Sports\ClimbQuantification;

use Runalyze\Sports\ClimbQuantification\ClimbByBikeIndex;

class ClimbByBikeIndexTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClimbByBikeIndex */
    protected $Cbb;

    public function setUp()
    {
        $this->Cbb = new ClimbByBikeIndex();
    }

    public function testSimpleValues()
    {
        $testMatrix = [
            [1.0, 10, 3.1],
            [1.0, 50, 13.5],
            [1.0, 100, 31.0],
            [2.0, 50, 8.25],
            [2.0, 100, 17.0],
            [2.0, 150, 28.25],
            [3.0, 150, 20.5],
            [3.0, 200, 29.7],
            [3.0, 300, 53.0],
            [5.0, 100, 11.0],
            [5.0, 200, 21.0],
            [5.0, 250, 27.5],
            [10.0, 300, 25.0],
            [10.0, 500, 45.0],
            [10.0, 750, 81.25],
            [15.0, 1000, 95.0],
        ];

        foreach ($testMatrix as $testValues) {
            $this->assertEquals(
                $testValues[2],
                $this->Cbb->getScoreFor($testValues[0], $testValues[1]),
                sprintf('Score for %.2f km with %u m fails.', $testValues[0], $testValues[1]),
                0.05
            );
        }
    }

    public function testThatAltitudeAtTopIsRespected()
    {
        $this->assertEquals(
            $this->Cbb->getScoreFor(7.0, 350) + 10.0,
            $this->Cbb->getScoreFor(7.0, 350, 2000)
        );

        $this->assertEquals(
            $this->Cbb->getScoreFor(7.0, 350) + 17.43,
            $this->Cbb->getScoreFor(7.0, 350, 2743)
        );
    }
}
