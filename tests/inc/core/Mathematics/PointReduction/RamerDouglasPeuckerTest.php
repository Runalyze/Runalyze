<?php

namespace Runalyze\Tests\Mathematics\PointReduction;

use Runalyze\Mathematics\PointReduction\RamerDouglasPeucker;

class RamerDouglasPeuckerTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleArrayWithEpsilonEqualsZero()
    {
        $algorithm = new RamerDouglasPeucker(
            range(0, 9 * 50, 50),
            [0, 2, 4, 6, 5, 7, 4, 8, 10, 0],
            0.0
        );

        $this->assertEquals([0, 150, 200, 250, 300, 350, 400, 450], $algorithm->getReducedX());
        $this->assertEquals([0, 6, 5, 7, 4, 8, 10, 0], $algorithm->getReducedY());
        $this->assertEquals([0, 3, 4, 5, 6, 7, 8, 9], $algorithm->getReducedIndices());
    }

    public function testSimpleArrayWithEpsilonEqualsOne()
    {
        $algorithm = new RamerDouglasPeucker(
            range(0, 9 * 50, 50),
            [0, 2, 4, 6, 5, 7, 4, 8, 10, 0],
            1.0
        );

        $this->assertEquals([0, 150, 200, 250, 300, 400, 450], $algorithm->getReducedX());
        $this->assertEquals([0, 6, 5, 7, 4, 10, 0], $algorithm->getReducedY());
        $this->assertEquals([0, 3, 4, 5, 6, 8, 9], $algorithm->getReducedIndices());
    }

    public function testSimpleArrayWithEpsilonEqualsTwo()
    {
        $algorithm = new RamerDouglasPeucker(
            range(0, 9 * 50, 50),
            [0, 2, 4, 6, 5, 7, 4, 8, 10, 0],
            2.0
        );

        $this->assertEquals([0, 150, 250, 300, 400, 450], $algorithm->getReducedX());
        $this->assertEquals([0, 6, 7, 4, 10, 0], $algorithm->getReducedY());
        $this->assertEquals([0, 3, 5, 6, 8, 9], $algorithm->getReducedIndices());
    }

    public function testMoreComplicatedExample()
    {
        $algorithm = new RamerDouglasPeucker(
            range(0, 20 * 50, 50),
            [0, 2, 4, 6, 15, 27, 25, 18, 13, 58, 95, 94, 91, 100, 105, 127, 15, 125, 67, 65, 0],
            10.0
        );

        $this->assertEquals([0, 6, 27, 13, 95, 91, 127, 15, 125, 67, 65, 0], $algorithm->getReducedY());
        $this->assertEquals([0, 3, 5, 8, 10, 12, 15, 16, 17, 18, 19, 20], $algorithm->getReducedIndices());
    }
}
