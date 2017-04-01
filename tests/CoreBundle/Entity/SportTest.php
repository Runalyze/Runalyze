<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Profile\Sport\Generic;
use Runalyze\Profile\Sport\SportProfile;

class SportTest extends \PHPUnit_Framework_TestCase
{
    /** @var Sport */
    protected $Sport;

    public function setUp()
    {
        $this->Sport = new Sport();
    }

    public function testEmptyEntity()
    {
        $this->assertEquals(PaceEnum::KILOMETER_PER_HOUR, $this->Sport->getSpeed());

        $this->assertFalse($this->Sport->hasInternalSportId());
        $this->assertInstanceOf(Generic::class, $this->Sport->getInternalSport());
        $this->assertTrue($this->Sport->getInternalSport()->isCustom());
    }

    public function testRunningAndCycling()
    {
        $this->Sport->setInternalSportId(SportProfile::RUNNING);

        $this->assertTrue($this->Sport->hasInternalSportId());
        $this->assertTrue($this->Sport->getInternalSport()->isRunning());

        $this->Sport->setInternalSportId(SportProfile::CYCLING);

        $this->assertTrue($this->Sport->hasInternalSportId());
        $this->assertTrue($this->Sport->getInternalSport()->isCycling());
    }
}
