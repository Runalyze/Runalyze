<?php

namespace Runalyze\Tests\Parser\Activity\Common\Data\Round;

use Runalyze\Parser\Activity\Common\Data\Round\Round;

class RoundTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleRound()
    {
        $round = new Round(1.0, 265);

        $this->assertEquals(1.0, $round->getDistance());
        $this->assertEquals(265, $round->getDuration());
        $this->assertTrue($round->isActive());
    }

    public function testRestingRound()
    {
        $round = new Round(1.0, 265, false);

        $this->assertFalse($round->isActive());
    }

    public function testSetter()
    {
        $round = new Round(1.0, 105, false);
        $round->setDistance(0.5);
        $round->setDuration(104);
        $round->setActive();

        $this->assertEquals(0.5, $round->getDistance());
        $this->assertEquals(104, $round->getDuration());
        $this->assertTrue($round->isActive());
    }
}
