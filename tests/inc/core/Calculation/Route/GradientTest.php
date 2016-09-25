<?php

namespace Runalyze\Calculation\Route;

use Runalyze\Model;
use Runalyze\Calculation\Math\MovingAverage\Kernel;

class GradientTest extends \PHPUnit_Framework_TestCase
{
    public function testWithoutInputData()
    {
        $gradient = new Gradient();
        $gradient->calculate();

        $this->assertEmpty($gradient->getSeries());
    }

    /** @expectedException \InvalidArgumentException */
    public function testWithUnbalancedInputData()
    {
        new Gradient(
            [100, 105, 110],
            [1.0, 2.0, 3.0, 4.0]
        );
    }

    /** @expectedException \InvalidArgumentException */
    public function testWithEmptyTrackdata()
    {
        $gradient = new Gradient();
        $gradient->setDataFrom(
            new Model\Route\Entity([
                Model\Route\Entity::ELEVATIONS_ORIGINAL => [100, 105, 110]
            ]),
            new Model\Trackdata\Entity()
        );
    }

    public function testWithDataFromModel()
    {
        $gradient = new Gradient();
        $gradient->setDataFrom(
            new Model\Route\Entity([
                Model\Route\Entity::ELEVATIONS_ORIGINAL => [100, 150, 250]
            ]),
            new Model\Trackdata\Entity([
                Model\Trackdata\Entity::DISTANCE => [1.0, 2.0, 2.5]
            ])
        );
        $gradient->calculate();

        $this->assertEquals(
            [5.0, 5.0, 20.0],
            $gradient->getSeries()
        );
    }

    public function testWithDataFromSetter()
    {
        $gradient = new Gradient();
        $gradient->setData(
            [10, 75, 100],
            [1.0, 2.0, 4.0]
        );
        $gradient->calculate();

        $this->assertEquals(
            [6.5, 6.5, 1.25],
            $gradient->getSeries()
        );
    }

    public function testWithKernel()
    {
        $gradient = new Gradient(
            [10, 20, 50, 40, 75, 100, 150, 120, 125, 110],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        );
        $gradient->setMovingAverageKernel(new Kernel\Uniform(4));
        $gradient->calculate();

        $this->assertEquals(
            [0.6, 0.6, 1.3, 1.8, 2.6, 1.4, 1.7, 0.7, 0.2, -0.8],
            $gradient->getSeries()
        );
    }
}
