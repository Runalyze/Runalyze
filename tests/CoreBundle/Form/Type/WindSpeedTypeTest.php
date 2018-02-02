<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Form\Type;

use Runalyze\Bundle\CoreBundle\Form\Type\WindSpeedType;
use Runalyze\Metrics\Velocity\Unit\KilometerPerHour;
use Runalyze\Metrics\Velocity\Unit\MilesPerHour;

class WindSpeedTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformWithKilometerPerHour()
    {
        $type = new WindSpeedType(new KilometerPerHour());

        $this->assertEquals(null, $type->transform(null));
        $this->assertEquals(0, $type->transform(0));
        $this->assertEquals(11, $type->transform(11));
        $this->assertEquals(27, $type->transform(27));
        $this->assertEquals(81, $type->transform(81));
    }

    public function testReverseTransformWithKilometerPerHour()
    {
        $type = new WindSpeedType(new KilometerPerHour());

        $this->assertEquals(null, $type->reverseTransform(''));
        $this->assertEquals(0, $type->reverseTransform('0'));
        $this->assertEquals(11, $type->reverseTransform('11'));
        $this->assertEquals(27, $type->reverseTransform('27'));
        $this->assertEquals(81, $type->reverseTransform('81'));
    }

    public function testTransformWithMilesPerHour()
    {
        $type = new WindSpeedType(new MilesPerHour());

        $this->assertEquals(null, $type->transform(null));
        $this->assertEquals(0, $type->transform(0));
        $this->assertEquals(7, $type->transform(11));
        $this->assertEquals(17, $type->transform(27));
        $this->assertEquals(50, $type->transform(81));
    }

    public function testReverseTransformWithMilesPerHour()
    {
        $type = new WindSpeedType(new MilesPerHour());

        $this->assertEquals(null, $type->reverseTransform(''));
        $this->assertEquals(0, $type->reverseTransform('0'));
        $this->assertEquals(18, $type->reverseTransform('11'));
        $this->assertEquals(43, $type->reverseTransform('27'));
        $this->assertEquals(130, $type->reverseTransform('81'));
    }
}
