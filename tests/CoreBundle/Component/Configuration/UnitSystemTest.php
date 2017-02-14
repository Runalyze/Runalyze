<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Configuration;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;
use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Metrics\Velocity\Unit\SecondsPer500y;
use Runalyze\Metrics\Velocity\Unit\SecondsPerMile;
use Runalyze\Parameter\Application\PaceUnit;

class UnitSystemTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllUnitsAreAccessible()
    {
        $unitSystem = new UnitSystem(new RunalyzeConfigurationList());

        $unitSystem->getDistanceUnit();
        $unitSystem->getEnergyUnit();
        $unitSystem->getHeartRateUnit();
        $unitSystem->getPaceUnit();
        $unitSystem->getTemperatureUnit();
        $unitSystem->getWeightUnit();
    }

    public function testThatPaceUnitCanBeSet()
    {
        $unitSystem = new UnitSystem(new RunalyzeConfigurationList());
        $unitSystem->setPaceUnit(new SecondsPer500y());

        $this->assertInstanceOf(SecondsPer500y::class, $unitSystem->getPaceUnit());
    }

    public function testThatPaceUnitCanBeSetFromSport()
    {
        $sport = new Sport();
        $sport->setSpeed(PaceUnit::MIN_PER_MILE);

        $unitSystem = new UnitSystem(new RunalyzeConfigurationList());
        $unitSystem->setPaceUnitFromSport($sport);

        $this->assertInstanceOf(SecondsPerMile::class, $unitSystem->getPaceUnit());
    }
}
