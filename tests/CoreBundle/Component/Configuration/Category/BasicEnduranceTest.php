<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Configuration\Category;

use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\BasicEndurance;

class BasicEnduranceTest extends \PHPUnit_Framework_TestCase
{
    public function testDaysToConsiderForWeeklyMileageAdaptedForFirstActivityDate()
    {
        $config = new BasicEndurance();

        $this->assertEquals(182, $config->getDaysToConsiderForWeeklyMileage());
        $this->assertEquals(182, $config->getDaysToConsiderForWeeklyMileage(365));
        $this->assertEquals(150, $config->getDaysToConsiderForWeeklyMileage(150));
        $this->assertEquals(92, $config->getDaysToConsiderForWeeklyMileage(92));
        $this->assertEquals(70, $config->getDaysToConsiderForWeeklyMileage(42));
        $this->assertEquals(70, $config->getDaysToConsiderForWeeklyMileage(0));
    }
}
