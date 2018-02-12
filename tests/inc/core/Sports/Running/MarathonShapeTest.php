<?php

namespace Runalyze\Tests\Sports\Running;

use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\BasicEndurance;
use Runalyze\Sports\Running\MarathonShape;

class MarathonShapeTest extends \PHPUnit_Framework_TestCase
{
    protected function getDefaultConfiguration()
    {
        return new BasicEndurance();
    }

    public function testThatVerySmallVO2maxValuesAreIgnored()
    {
        $shape = new MarathonShape(1.0, $this->getDefaultConfiguration());

        $this->assertEquals(22.0, $shape->getTargetForLongJogEachWeek(), '', 0.1);
        $this->assertEquals(38.0, $shape->getTargetForWeeklyMileage(), '', 1.0);

        $shape->setEffectiveVO2max(20.0);

        $this->assertEquals(22.0, $shape->getTargetForLongJogEachWeek(), '', 0.1);
        $this->assertEquals(38.0, $shape->getTargetForWeeklyMileage(), '', 1.0);
    }

    public function testSimpleExample()
    {
        $config = $this->getDefaultConfiguration();
        $config->set('BE_DAYS_FOR_LONGJOGS', '7');
        $config->set('BE_DAYS_FOR_WEEK_KM', '7');
        $config->set('BE_PERCENTAGE_WEEK_KM', '0.50');

        $shape = new MarathonShape(60.0, $config);

        $this->assertEquals(32.5, $shape->getTargetForLongJogEachWeek(), '', 1.0);
        $this->assertEquals(104.0, $shape->getTargetForWeeklyMileage(), '', 1.0);
        $this->assertEquals(75.0, $shape->getShapeFor(52.0, 1.0));
        $this->assertEquals(75.0, $shape->getShapeFor(104.0, 0.5));
        $this->assertEquals(100.0, $shape->getShapeFor(104.0, 1.0));
        $this->assertEquals(125.0, $shape->getShapeFor(52.0, 2.0));
        $this->assertEquals(100.0, $shape->getShapeFor(0.0, 2.0));
    }
}
