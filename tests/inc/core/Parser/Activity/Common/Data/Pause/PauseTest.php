<?php

namespace Runalyze\Tests\Parser\Activity\Common\Data\Pause;

use Runalyze\Parser\Activity\Common\Data\Pause\Pause;

class PauseTest extends \PHPUnit_Framework_TestCase
{
    /** @var Pause */
    protected $Pause;

    public function setUp()
    {
        $this->Pause = new Pause(42, 314);
    }

    public function testConstructor()
    {
        $this->assertEquals(42, $this->Pause->getTimeIndex());
        $this->assertEquals(314, $this->Pause->getDuration());

        $this->assertFalse($this->Pause->hasHeartRateDetails());
        $this->assertNull($this->Pause->getHeartRateAtStart());
        $this->assertNull($this->Pause->getHeartRateAtEnd());

        $this->assertFalse($this->Pause->hasRecoveryDetails());
        $this->assertNull($this->Pause->getHeartRateAtRecovery());
        $this->assertNull($this->Pause->getTimeUntilRecovery());

        $this->assertFalse($this->Pause->hasPerformanceCondition());
        $this->assertNull($this->Pause->getPerformanceCondition());
    }

    public function testHeartRateDetails()
    {
        $this->Pause->setHeartRateDetails(163, 142);

        $this->assertTrue($this->Pause->hasHeartRateDetails());
        $this->assertEquals(163, $this->Pause->getHeartRateAtStart());
        $this->assertEquals(142, $this->Pause->getHeartRateAtEnd());
    }

    public function testHeartRateRecoveryDetails()
    {
        $this->Pause->setRecoveryDetails(112, 120);

        $this->assertTrue($this->Pause->hasRecoveryDetails());
        $this->assertEquals(112, $this->Pause->getHeartRateAtRecovery());
        $this->assertEquals(120, $this->Pause->getTimeUntilRecovery());
    }

    public function testPerformanceCondition()
    {
        $this->Pause->setPerformanceCondition(-4);

        $this->assertTrue($this->Pause->hasPerformanceCondition());
        $this->assertEquals(-4, $this->Pause->getPerformanceCondition());
    }
}
