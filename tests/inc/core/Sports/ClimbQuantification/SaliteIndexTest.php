<?php

namespace Runalyze\Tests\Sports\ClimbQuantification;

use Runalyze\Sports\ClimbQuantification\SaliteIndex;

class SaliteIndexTest extends \PHPUnit_Framework_TestCase
{
    /** @var SaliteIndex */
    protected $Salite;

    public function setUp()
    {
        $this->Salite = new SaliteIndex();
    }

    public function testSimpleValues()
    {
        $testMatrix = [
            [1.0, 10, 1],
        ];

        foreach ($testMatrix as $testValues) {
            $this->assertEquals(
                $testValues[2],
                $this->Salite->getScoreFor($testValues[0], $testValues[1]),
                sprintf('Score for %.2f km with %u m fails.', $testValues[0], $testValues[1]),
                0.5
            );
        }
    }

    public function testThatAltitudeAtTopDoesNotMatter()
    {
        $this->assertEquals(
            $this->Salite->getScoreFor(7.0, 350),
            $this->Salite->getScoreFor(7.0, 350, 4000)
        );
    }

    public function testPassoStelvioWithoutExactProfile()
    {
        $this->assertEquals(1336, $this->Salite->getScoreFor(25.4, 1842), '', 0.5);
    }

    /**
     * @see http://www.salite.ch/struttura/indice_diff.asp
     */
    public function testPassoStelvioWithExactProfile()
    {
        $this->assertEquals(
            1417,
            $this->Salite->getScoreForProfile([
                [1.0, 0.018],
                [3.5, 0.0523],
                [1.5, 0.0613],
                [0.73, 0.0658],
                [1.0, 0.0480],
                [1.0, 0.0780],
                [1.0, 0.0780],
                [1.0, 0.0660],
                [1.0, 0.0740],
                [1.0, 0.0880],
                [1.0, 0.0770],
                [1.0, 0.0820],
                [1.0, 0.0890],
                [1.0, 0.0870],
                [1.0, 0.0820],
                [1.0, 0.0990],
                [1.0, 0.0830],
                [1.0, 0.0840],
                [1.0, 0.0870],
                [1.0, 0.0770],
                [1.0, 0.0860],
                [1.4, 0.0886],
            ]),
            '',
            5
        );
    }
}
