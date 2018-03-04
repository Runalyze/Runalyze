<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\VO2maxCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Training;

class VO2maxCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var VO2maxCalculator */
    protected $Calculator;

    /** @var Training */
    protected $Activity;

    protected function setUp()
    {
        $this->Calculator = new VO2maxCalculator();
        $this->Activity = new Training();
        $this->Activity->setRoute(new Route());
    }

    protected function setDataToActivity($duration, $distance, $heartRate)
    {
        $this->Activity->setS($duration);
        $this->Activity->setDistance($distance);
        $this->Activity->setPulseAvg($heartRate);
    }

    public function testCalculationForEmptyActivity()
    {
        $this->Calculator->calculateFor($this->Activity, 200, 0, 0);

        $this->assertEquals(0.0, $this->Activity->getVO2maxByTime());
        $this->assertEquals(0.0, $this->Activity->getVO2max());
        $this->assertEquals(0.0, $this->Activity->getVO2maxWithElevation());
    }

    public function testCalculationForSimpleActivityAtExpectedHeartRate()
    {
        $this->setDataToActivity(2481, 10.0, 190);
        $this->Calculator->calculateFor($this->Activity, 200, 0, 0);

        $this->assertEquals(50.0, $this->Activity->getVO2maxByTime(), '', 0.1);
        $this->assertEquals(50.0, $this->Activity->getVO2max(), '', 0.5);
        $this->assertEquals($this->Activity->getVO2max(), $this->Activity->getVO2maxWithElevation(), '', 0.01);
    }

    public function testCalculationForSimpleActivityAtLowerHeartRate()
    {
        $this->setDataToActivity(2481, 10.0, 170);
        $this->Calculator->calculateFor($this->Activity, 200, 0, 0);

        $this->assertEquals(50.0, $this->Activity->getVO2maxByTime(), '', 0.1);
        $this->assertGreaterThan(55.0, $this->Activity->getVO2max());
    }

    public function testCalculationForSimpleElevationCorrection()
    {
        $this->setDataToActivity(2481, 10.0, 170);
        $this->Activity->setElevation(120);

        $this->Calculator->calculateFor($this->Activity, 200, 0, 0);
        $vo2maxWithoutCorrection = $this->Activity->getVO2maxWithElevation();

        $this->Calculator->calculateFor($this->Activity, 200, 3, -1);
        $vo2maxWithCorrection = $this->Activity->getVO2maxWithElevation();

        $this->assertGreaterThan($vo2maxWithoutCorrection, $vo2maxWithCorrection);

        $this->Activity->getRoute()->setElevation(150);

        $this->Calculator->calculateFor($this->Activity, 200, 3, -1);
        $vo2maxWithCorrectionViaRoute = $this->Activity->getVO2maxWithElevation();

        $this->assertGreaterThan($vo2maxWithCorrection, $vo2maxWithCorrectionViaRoute);

        $this->Activity->getRoute()->setElevationUp(150);
        $this->Activity->getRoute()->setElevationDown(0);

        $this->Calculator->calculateFor($this->Activity, 200, 3, -1);
        $vo2maxWithDetailedCorrectionViaRoute = $this->Activity->getVO2maxWithElevation();

        $this->assertGreaterThan($vo2maxWithCorrectionViaRoute, $vo2maxWithDetailedCorrectionViaRoute);
    }
}
