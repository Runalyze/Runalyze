<?php

namespace Runalyze\Tests\Sports\Performance;

use Runalyze\Sports\Performance\Monotony;

class MonotonyTest extends \PHPUnit_Framework_TestCase
{

    public function testEmptyArray()
    {
        $monotony = new Monotony([]);
        $monotony->calculate();

        $this->assertEquals(0, $monotony->value());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRuntimeException()
    {
        $monotony = new Monotony([100]);
        $monotony->value();
    }

    public function testSingleValue()
    {
        $monotony = new Monotony([1, 1, 1, 1, 1, 1, 1]);
        $monotony->calculate();

        $this->assertEquals(Monotony::MAX, $monotony->value());
    }

    public function testSimpleExample()
    {
        $monotony = new Monotony([0, 20, 10, 10, 10, 10, 10]);
        $monotony->calculate();

        $this->assertEquals(10 / sqrt(200 / 7), $monotony->value());
        $this->assertEquals(7 * 10 * 10 / sqrt(200 / 7), $monotony->trainingStrain());
    }

    public function testAnotherSimpleExample()
    {
        $monotony = new Monotony([5, 15, 20, 20, 20, 25, 35]);
        $monotony->calculate();

        $this->assertEquals(20 / 8.45, $monotony->value(), '', 0.01);
        $this->assertEquals(7 * 20 * 20 / 8.45, $monotony->trainingStrain(), '', 0.1);
    }

    public function testEmptyDays()
    {
        $withZeroes = new Monotony([0, 0, 0, 10, 20, 30, 40]);
        $withZeroes->calculate();

        $withoutZeroes = new Monotony([10, 20, 30, 40]);
        $withoutZeroes->calculate();

        $this->assertEquals($withZeroes->value(), $withoutZeroes->value());
        $this->assertEquals($withZeroes->trainingStrain(), $withoutZeroes->trainingStrain());
    }
}
