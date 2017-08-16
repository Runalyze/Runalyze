<?php

namespace Runalyze\Tests\Sports\Performance\Model;

use Runalyze\Sports\Performance\Model\AbstractModel;
use Runalyze\Sports\Performance\Model\TsbModel;

class TsbModelTest extends \PHPUnit_Framework_TestCase
{
    public function testSingleWorkout()
    {
        $Model = new TsbModel(
            [0 => 1000],
            42, 7
        );
        $Model->calculate();

        $this->assertEquals(47, $Model->fitnessAt(0));
        $this->assertEquals(250, $Model->fatigueAt(0));
        $this->assertEquals(-203, $Model->performanceAt(0));
        $this->assertEquals(7.0, $Model->restDaysAt(0), '', 0.5);
    }

    public function testSimpleExample()
    {
        $Model = new TsbModel([
            0 => 150,
            1 => 50,
            2 => 40,
            5 => 60,
            6 => 30,
            7 => 100
        ], 42, 7);
        $Model->calculate();

        $this->assertEquals(7, $Model->fitnessAt(0));
        $this->assertEquals(38, $Model->fatigueAt(0));
        $this->assertEquals(-31, $Model->performanceAt(0));

        $this->assertEquals(9, $Model->fitnessAt(1));
        $this->assertEquals(10, $Model->fitnessAt(2));
        $this->assertEquals(10, $Model->fitnessAt(3));
        $this->assertEquals(9, $Model->fitnessAt(4));
        $this->assertEquals(12, $Model->fitnessAt(5));
        $this->assertEquals(13, $Model->fitnessAt(6));
        $this->assertEquals(17, $Model->fitnessAt(7));

        $this->assertEquals(41, $Model->fatigueAt(1));
        $this->assertEquals(40, $Model->fatigueAt(2));
        $this->assertEquals(30, $Model->fatigueAt(3));
        $this->assertEquals(23, $Model->fatigueAt(4));
        $this->assertEquals(32, $Model->fatigueAt(5));
        $this->assertEquals(32, $Model->fatigueAt(6));
        $this->assertEquals(49, $Model->fatigueAt(7));
    }

    public function testSteadyState()
    {
        $Model = new TsbModel(array_fill(0, 101, 50), 42, 7);
        $Model->calculate();

        $this->assertEquals(50, $Model->fitnessAt(100));
        $this->assertEquals(50, $Model->fatigueAt(100));

        $this->assertEquals(50, $Model->fitnessAt(70), '', 2.5);
        $this->assertEquals(50, $Model->fatigueAt(14), '', 2.5);

        $this->assertEquals(50, $Model->fitnessAt(50), '', 5.0);
        $this->assertEquals(50, $Model->fatigueAt(10), '', 5.0);

        $this->assertEquals(50, $Model->fitnessAt(42), '', 7.5);
        $this->assertEquals(50, $Model->fatigueAt(7), '', 7.5);
    }

    public function testFutureFitness()
    {
        $Model = new TsbModel([0 => 1000], 42, 7);
        $Model->setRange(0, 100);
        $Model->calculate();

        $this->assertEquals(44, $Model->fitnessAt(1));
        $this->assertEquals(42, $Model->fitnessAt(2));
        $this->assertEquals(37, $Model->fitnessAt(5));
        $this->assertEquals(29, $Model->fitnessAt(10));
        $this->assertEquals(23, $Model->fitnessAt(15));
        $this->assertEquals(18, $Model->fitnessAt(20));
        $this->assertEquals(11, $Model->fitnessAt(30));
        $this->assertEquals(7, $Model->fitnessAt(40));
        $this->assertEquals(4, $Model->fitnessAt(50));
        $this->assertEquals(3, $Model->fitnessAt(60));
        $this->assertEquals(2, $Model->fitnessAt(70));
        $this->assertEquals(1, $Model->fitnessAt(80));
        $this->assertEquals(1, $Model->fitnessAt(90));
        $this->assertEquals(0, $Model->fitnessAt(100));
    }

    public function testFutureFatigue()
    {
        $Model = new TsbModel([0 => 1000], 42, 7);
        $Model->setRange(0, 30);
        $Model->calculate();

        $this->assertEquals(188, $Model->fatigueAt(1));
        $this->assertEquals(141, $Model->fatigueAt(2));
        $this->assertEquals(59, $Model->fatigueAt(5));
        $this->assertEquals(14, $Model->fatigueAt(10));
        $this->assertEquals(3, $Model->fatigueAt(15));
        $this->assertEquals(1, $Model->fatigueAt(20));
        $this->assertEquals(0, $Model->fatigueAt(22));
    }

    public function testBadTimeConstants()
    {
        $Model = new TsbModel([1000, 500], 14, 14);
        $Model->setRange(0, 10);
        $Model->calculate();

        $this->assertEquals(0, $Model->restDays(50, 100));
        $this->assertEquals(0, $Model->maxTrimpToBalanced(50, 100));
    }

    public function testExtremeValuesAtBeginning()
    {
        $model = new TsbModel([100, 100, 100, 100, 100, 100, 100, 0, 0, 0, 0, 0, 0, 0], 42, 7);
        $model->calculate();

        $this->assertEquals([
                AbstractModel::FITNESS => [5, 9, 13, 17, 21, 25, 28, 27, 26, 25, 23, 22, 21, 20],
                AbstractModel::FATIGUE => [25, 44, 58, 68, 76, 82, 87, 65, 49, 37, 27, 21, 15, 12],
                AbstractModel::PERFORMANCE => [-20, -35, -44, -51, -55, -57, -58, -38, -23, -12, -4, 2, 6, 9],
            ], array_map(function($a) {
                return array_map(function($v) { return (int)round($v); }, $a);
            }, $model->getArrays())
        );

        $restDays = [];
        for ($i = 0; $i < 14; ++$i) {
            $restDays[] = round($model->restDaysAt($i), 1);
        }

        $this->assertEquals([
            6.7, 6.6, 6.2, 5.8, 5.4, 4.9, 4.7, 3.7, 2.6, 1.6, 0.7, 0.0, 0.0, 0.0
        ], $restDays);
    }
}
