<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;

class TrackdataTest extends \PHPUnit_Framework_TestCase
{
    /** @var Trackdata */
    protected $Data;

    public function setUp()
    {
        $this->Data = new Trackdata();
    }

    public function testEmptyEntity()
    {
        $this->assertTrue($this->Data->isEmpty());
        $this->assertNull($this->Data->getTime());
        $this->assertNull($this->Data->getDistance());
        $this->assertInstanceOf(PauseCollection::class, $this->Data->getPauses());
        $this->assertTrue($this->Data->getPauses()->isEmpty());
    }

    public function testLazyArraysForEmptyEntity()
    {
        $this->assertFalse($this->Data->hasPace());
        $this->assertFalse($this->Data->hasStrideLength());
        $this->assertFalse($this->Data->hasVerticalRatio());

        $this->assertNull($this->Data->getPace());
        $this->assertNull($this->Data->getGradient());
        $this->assertNull($this->Data->getGradeAdjustedPace());
        $this->assertNull($this->Data->getStrideLength());
        $this->assertNull($this->Data->getVerticalRatio());
    }

    public function testThatPaceIsAlwaysUpToDate()
    {
        $this->assertFalse($this->Data->hasPace());
        $this->assertNull($this->Data->getPace());

        $this->Data->setDistance([0.0, 1.0, 2.0, 3.0]);
        $this->Data->setTime([0, 300, 660, 900]);

        $this->assertTrue($this->Data->hasPace());
        $this->assertEquals([300, 300, 360, 240], $this->Data->getPace());
    }

    public function testCalculationOfLazyArrays()
    {
        $this->Data->setTime([0, 300, 600, 900]);
        $this->Data->setDistance([0.0, 1.0, 2.0, 3.0]);
        $this->Data->setCadence([90, 90, 90, 90]);
        $this->Data->setVerticalOscillation([110, 110, 110, 110]);

        $this->Data->setActivity(new Training());
        $this->Data->getActivity()->setRoute(new Route());
        $this->Data->getActivity()->getRoute()->setElevationsCorrected([0, 50, 100, 50]);

        $this->assertEquals([111, 111, 111, 111], $this->Data->getStrideLength());
        $this->assertEquals([99, 99, 99, 99], $this->Data->getVerticalRatio());
        $this->assertEquals([300, 300, 300, 300], $this->Data->getPace());
    }

    public function testCalculationOfGradientAndGradeAdjustedPace()
    {
        $this->Data->setTime(range(0, 900, 15));
        $this->Data->setDistance(range(0.0, 3.0, 0.05));

        $this->Data->setActivity(new Training());
        $this->Data->getActivity()->setRoute(new Route());
        $this->Data->getActivity()->getRoute()->setElevationsCorrected(
            array_fill(0, 10, 0) +
            array_fill(10, 20, 50) +
            array_fill(30, 20, 100) +
            array_fill(50, 11, 50)
        );

        $gradeAdjustedPace = $this->Data->getGradeAdjustedPace();
        $gradient = $this->Data->getGradient();

        $this->assertLessThan(300, $gradeAdjustedPace[10]);
        $this->assertLessThan(300, $gradeAdjustedPace[30]);
        $this->assertGreaterThan(300, $gradeAdjustedPace[50]);

        $this->assertEquals(5.0, $gradient[10], '', 0.5);
        $this->assertEquals(5.0, $gradient[30], '', 0.5);
        $this->assertEquals(-5.0, $gradient[50], '', 0.5);

        $this->assertEquals(array_fill(0, 61, 300), $this->Data->getPace());
    }
}
