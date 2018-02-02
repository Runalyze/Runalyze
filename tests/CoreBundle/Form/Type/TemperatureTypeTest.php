<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Form\Type;

use Runalyze\Bundle\CoreBundle\Form\Type\TemperatureType;
use Runalyze\Metrics\Temperature\Unit\Fahrenheit;

class TemperatureTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testReverseTransformWithFahrenheit()
    {
        $type = new TemperatureType(new Fahrenheit());

        $this->assertEquals(-18, (int)$type->reverseTransform('0'));
        $this->assertEquals(8, $type->reverseTransform('46'));
        $this->assertEquals(9, $type->reverseTransform('48'));
        $this->assertEquals(10, $type->reverseTransform('50'));
        $this->assertEquals(13, $type->reverseTransform('56'));
    }
}
