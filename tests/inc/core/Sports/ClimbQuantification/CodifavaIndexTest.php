<?php

namespace Runalyze\Tests\Sports\ClimbQuantification;

use Runalyze\Sports\ClimbQuantification\CodifavaIndex;

class CodifavaIndexTest extends \PHPUnit_Framework_TestCase
{
    /** @var CodifavaIndex */
    protected $Codifava;

    public function setUp()
    {
        $this->Codifava = new CodifavaIndex();
    }

    public function testSimpleValues()
    {
        $testMatrix = [
            [1.0, 10, 4.1],
            [1.0, 50, 22.5],
            [1.0, 100, 50.0],
            [2.0, 50, 11.25],
            [2.0, 100, 25.0],
            [2.0, 150, 41.25],
            [3.0, 150, 27.5],
            [3.0, 200, 40.0],
            [3.0, 300, 70.0],
            [5.0, 100, 10.0],
            [5.0, 200, 24.0],
            [5.0, 250, 32.5],
            [10.0, 300, 21.0],
            [10.0, 500, 45.0],
            [10.0, 750, 86.25],
            [15.0, 1000, 93.3],
        ];

        foreach ($testMatrix as $testValues) {
            $this->assertEquals(
                $testValues[2],
                $this->Codifava->getScoreFor($testValues[0], $testValues[1]),
                sprintf('Score for %.2f km with %u m fails.', $testValues[0], $testValues[1]),
                0.1
            );
        }
    }

    public function testAdditiveCalculationForExactProfile()
    {
        $this->assertEquals(
            $this->Codifava->getScoreFor(10.0, 500),
            $this->Codifava->getScoreForProfile([[5.0, 0.05], [5.0, 0.05]])
        );

        $this->assertEquals(45, $this->Codifava->getScoreForProfile([[5.0, 0.05], [5.0, 0.05]]), '', 0.1);
        $this->assertEquals(60, $this->Codifava->getScoreForProfile([[2.5, 0.10], [7.5, 0.0333]]), '', 0.1);
    }
}
