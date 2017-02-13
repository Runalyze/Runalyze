<?php

namespace Runalyze\Tests\Sports\Performance\Model;

use Runalyze\Sports\Performance\Model\BanisterModel;

class BanisterTest extends \PHPUnit_Framework_TestCase
{
    public function testSingleWorkout()
    {
        $Model = new BanisterModel([0 => 1000], 42, 7, 1, 2);
        $Model->calculate();

        $this->assertEquals(1000, $Model->fitnessAt(0));
        $this->assertEquals(1000, $Model->fatigueAt(0));
        $this->assertEquals(-1000, $Model->performanceAt(0));
    }

    public function testSimpleExample()
    {
        $Model = new BanisterModel([
            0 => 150,
            1 => 50,
            2 => 40,
            5 => 60,
            6 => 30,
            7 => 100
        ], 42, 7, 1, 2);
        $Model->calculate();

        $this->assertEquals(196, $Model->fitnessAt(1));
        $this->assertEquals(180, $Model->fatigueAt(1));
        $this->assertEquals(-164, $Model->performanceAt(1));

        $this->assertEquals(232, $Model->fitnessAt(2));
        $this->assertEquals(226, $Model->fitnessAt(3));
        $this->assertEquals(221, $Model->fitnessAt(4));
        $this->assertEquals(276, $Model->fitnessAt(5));
        $this->assertEquals(299, $Model->fitnessAt(6));
        $this->assertEquals(392, $Model->fitnessAt(7));

        $this->assertEquals(196, $Model->fatigueAt(2));
        $this->assertEquals(170, $Model->fatigueAt(3));
        $this->assertEquals(147, $Model->fatigueAt(4));
        $this->assertEquals(188, $Model->fatigueAt(5));
        $this->assertEquals(193, $Model->fatigueAt(6));
        $this->assertEquals(267, $Model->fatigueAt(7));
    }

    public function testNoSteadyState()
    {
        $Model = new BanisterModel(array_fill(0, 51, 100), 42, 7, 1, 2);
        $Model->calculate();

        $this->assertEquals(979, $Model->fitnessAt(10));
        $this->assertEquals(1672, $Model->fitnessAt(20));
        $this->assertEquals(2219, $Model->fitnessAt(30));
        $this->assertEquals(2649, $Model->fitnessAt(40));
        $this->assertEquals(2988, $Model->fitnessAt(50));

        $this->assertEquals(595, $Model->fatigueAt(10));
        $this->assertEquals(714, $Model->fatigueAt(20));
        $this->assertEquals(742, $Model->fatigueAt(30));
        $this->assertEquals(749, $Model->fatigueAt(40));
        $this->assertEquals(751, $Model->fatigueAt(50));
    }

    public function testFutureFitness()
    {
        $Model = new BanisterModel([0 => 1000], 42, 7, 1, 2);
        $Model->setRange(0, 100);
        $Model->calculate();

        $this->assertEquals(976, $Model->fitnessAt(1));
        $this->assertEquals(788, $Model->fitnessAt(10));
        $this->assertEquals(551, $Model->fitnessAt(25));
        $this->assertEquals(304, $Model->fitnessAt(50));
        $this->assertEquals(168, $Model->fitnessAt(75));
        $this->assertEquals(92, $Model->fitnessAt(100));
    }

    public function testFutureFatigue()
    {
        $Model = new BanisterModel([0 => 1000], 42, 7, 1, 2);
        $Model->setRange(0, 30);
        $Model->calculate();

        $this->assertEquals(867, $Model->fatigueAt(1));
        $this->assertEquals(490, $Model->fatigueAt(5));
        $this->assertEquals(240, $Model->fatigueAt(10));
        $this->assertEquals(117, $Model->fatigueAt(15));
        $this->assertEquals(57, $Model->fatigueAt(20));
        $this->assertEquals(28, $Model->fatigueAt(25));
    }
}
