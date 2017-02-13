<?php

namespace Runalyze\Tests\Sports\Performance\Model;

use Runalyze\Sports\Performance\Model\AbstractModel;

class AbstractModel_MockTesterFilled extends AbstractModel
{
    protected function prepareArrays()
    {
    }

    protected function finishArrays()
    {
    }

    protected function calculateArrays()
    {
        $this->Fitness = [-1 => 10, 0 => 15, 1 => 20];
        $this->Fatigue = [-1 => 15, 0 => 20, 1 => 15];
        $this->Performance = [-1 => -10, 0 => -10, 1 => 0];
    }
}

class AbstractModel_MockTesterEmpty extends AbstractModel
{
    protected function calculateArrays()
    {
        for ($i = $this->Range['from']; $i <= $this->Range['to']; ++$i) {
            $this->Fitness[$i] = 0;
            $this->Fatigue[$i] = 0;
            $this->Performance[$i] = 0;
        }
    }
}

class ModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractModel */
    protected $object;

    protected function setUp()
    {
        $this->object = new AbstractModel_MockTesterFilled([]);
        $this->object->calculate();
    }

    public function testRange()
    {
        $Model = new AbstractModel_MockTesterEmpty([]);
        $Model->setRange(-3, 4);
        $Model->calculate();

        $Expected = [];

        for ($i = -3; $i <= 4; ++$i) {
            $Expected[$i] = 0;
        }

        $this->assertEquals([
            AbstractModel::FITNESS => $Expected,
            AbstractModel::FATIGUE => $Expected,
            AbstractModel::PERFORMANCE => $Expected
        ], $Model->getArrays());
    }

    public function testGetArrays()
    {
        $this->assertEquals([
            AbstractModel::FITNESS => [-1 => 10, 0 => 15, 1 => 20],
            AbstractModel::FATIGUE => [-1 => 15, 0 => 20, 1 => 15],
            AbstractModel::PERFORMANCE => [-1 => -10, 0 => -10, 1 => 0]
        ], $this->object->getArrays());
    }

    public function testFitnessAt()
    {
        $this->assertEquals(10, $this->object->fitnessAt(-1));
        $this->assertEquals(15, $this->object->fitnessAt(0));
        $this->assertEquals(20, $this->object->fitnessAt(+1));
    }

    public function testFatigueAt()
    {
        $this->assertEquals(15, $this->object->fatigueAt(-1));
        $this->assertEquals(20, $this->object->fatigueAt(0));
        $this->assertEquals(15, $this->object->fatigueAt(+1));
    }

    public function testPerformanceAt()
    {
        $this->assertEquals(-10, $this->object->performanceAt(-1));
        $this->assertEquals(-10, $this->object->performanceAt(0));
        $this->assertEquals(0, $this->object->performanceAt(+1));
    }

    public function testMaximalValues()
    {
        $this->assertEquals(20, $this->object->maxFitness());
        $this->assertEquals(20, $this->object->maxFatigue());
        $this->assertEquals(0, $this->object->maxPerformance());
        $this->assertEquals(-10, $this->object->minPerformance());
    }
}
