<?php

namespace Runalyze\Calculation\Performance;

use Runalyze\Sports\Performance\Model\AbstractModel;
use Runalyze\Sports\Performance\Model\TsbModel;

class FakeAbstractModel extends AbstractModel
{
    public function calculateArrays()
    {
        foreach ($this->Trimp as $i => $Trimp) {
            $this->Fitness[$i] = 2 * $Trimp;
            $this->Fatigue[$i] = 5 * $Trimp;
        }
    }
}

class MaximumCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongClosure()
    {
        new MaximumCalculator(function () {
        }, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNoModelInClosure()
    {
        new MaximumCalculator(function () {
            return new \StdClass();
        }, []);
    }

    public function testTSBclosure()
    {
        new MaximumCalculator(function (array $array) {
            return new TsbModel($array);
        }, []);
    }

    public function testFakeModel()
    {
        $Calc = new MaximumCalculator(function (array $array) {
            return new FakeAbstractModel($array);
        }, [10, 0, 50, 100, 0, 75, 13]);

        $this->assertEquals(200, $Calc->maxFitness());
        $this->assertEquals(500, $Calc->maxFatigue());
        $this->assertEquals(100, $Calc->maxTrimp());
    }
}
