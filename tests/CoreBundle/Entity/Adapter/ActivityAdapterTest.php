<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity\Adapter;

use Runalyze\Bundle\CoreBundle\Entity\Adapter\ActivityAdapter;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Util\LocalTime;

class ActivityAdapterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Training */
    protected $Activity;

    /** @var ActivityAdapter */
    protected $Adapter;

    public function setUp()
    {
        $this->Activity = new Training();
        $this->Adapter = new ActivityAdapter($this->Activity);
    }

    public function testEmptyActivity()
    {
        $this->assertFalse($this->Adapter->isRunning());
        $this->assertFalse($this->Adapter->isCycling());
        $this->assertFalse($this->Adapter->isSwimming());
        $this->assertFalse($this->Adapter->isRelevantForCurrentEffectiveVO2maxShape(1));
        $this->assertFalse($this->Adapter->isRelevantForCurrentMarathonShape(1));
    }

    public function testAgeOfActivity()
    {
        $this->Activity->setTime((new LocalTime())->sub(new \DateInterval('P2D'))->getTimestamp());

        $this->assertFalse($this->Adapter->isNotOlderThanXDays(1));
        $this->assertTrue($this->Adapter->isNotOlderThanXDays(2));
    }

    public function testAgeOfActivityForNow()
    {
        $this->Activity->setTime(LocalTime::now());

        $this->assertEquals(0, $this->Adapter->getAgeOfActivity(), '', 1);
        $this->assertTrue($this->Adapter->isNotOlderThanXDays(0));
    }
}
