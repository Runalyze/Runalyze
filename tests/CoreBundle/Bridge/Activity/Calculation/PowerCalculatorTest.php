<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\PowerCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Profile\Sport\SportProfile;

class PowerCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var PowerCalculator */
    protected $Calculator;

    /** @var Training */
    protected $Activity;

    protected function setUp()
    {
        $this->Activity = new Training();
        $this->Activity->setSport(new Sport());
        $this->Calculator = new PowerCalculator();
    }

    protected function setDataToActivity(array $distance = null, array $elevation = null)
    {
        $this->Activity->getSport()->setInternalSportId(SportProfile::CYCLING);

        if (null !== $elevation) {
            $this->Activity->setRoute(new Route());
            $this->Activity->getRoute()->setElevationsCorrected($elevation);
        }

        if (null !== $distance) {
            $this->Activity->setTrackdata(new Trackdata());
            $this->Activity->getTrackdata()->setDistance($distance);
            $this->Activity->getTrackdata()->setTime(array_map(function ($v) { return 300.0 * $v; }, $distance));
        }
    }

    public function testEmptyActivity()
    {
        $this->Calculator->calculateFor($this->Activity);

        $this->assertNull($this->Activity->getPower());
        $this->assertNull($this->Activity->isPowerCalculated());
    }

    public function testActivityWithoutElevation()
    {
        $this->setDataToActivity([0.1, 0.2, 0.3, 0.4, 0.5], null);

        $this->Calculator->calculateFor($this->Activity);

        $this->assertNull($this->Activity->getPower());
        $this->assertNull($this->Activity->isPowerCalculated());
        $this->assertNull($this->Activity->getTrackdata()->getPower());
    }

    public function testActivityWithWrongSport()
    {
        $this->setDataToActivity([0.1, 0.2, 0.3, 0.4, 0.5], [100, 110, 120, 110, 100]);
        $this->Activity->getSport()->setInternalSportId(SportProfile::HIKING);

        $this->Calculator->calculateFor($this->Activity);

        $this->assertNull($this->Activity->getPower());
        $this->assertNull($this->Activity->isPowerCalculated());
        $this->assertNull($this->Activity->getTrackdata()->getPower());
    }

    public function testThatPowerDataFromDeviceIsNotOverridden()
    {
        $this->setDataToActivity([0.1, 0.2, 0.3, 0.4, 0.5], [100, 110, 120, 110, 100]);
        $this->Activity->setPowerCalculated(false);
        $this->Activity->getTrackdata()->setPower([42, 42, 42, 42, 42]);
        $this->Activity->setPower(314);

        $this->Calculator->calculateFor($this->Activity);

        $this->assertFalse($this->Activity->isPowerCalculated());
        $this->assertEquals(314, $this->Activity->getPower());
        $this->assertEquals([42, 42, 42, 42, 42], $this->Activity->getTrackdata()->getPower());
    }

    public function testSimpleExample()
    {
        $this->setDataToActivity([0.1, 0.2, 0.3, 0.4, 0.5], [100, 110, 120, 110, 100]);

        $this->Calculator->calculateFor($this->Activity);

        $this->assertGreaterThan(0, $this->Activity->getPower());
        $this->assertTrue($this->Activity->isPowerCalculated());
        $this->assertCount(5, $this->Activity->getTrackdata()->getPower());
    }
}
