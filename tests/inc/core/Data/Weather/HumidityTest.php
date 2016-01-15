<?php

namespace Runalyze\Data\Weather;

class HumidityTest extends \PHPUnit_Framework_TestCase
{
    /** @expectedException \InvalidArgumentException */
    public function testNonNumericValue()
    {
        new Humidity('foobar');
    }

    /** @expectedException \InvalidArgumentException */
    public function testNegativeValue()
    {
        new Humidity(-13);
    }

    /** @expectedException \InvalidArgumentException */
    public function testTooLargeValue()
    {
        new Humidity(123);
    }

    public function testNull()
    {
        $Humidity = new Humidity();

        $this->assertEquals(null, $Humidity->value());
        $this->assertEquals('', $Humidity->string());
        $this->assertTrue($Humidity->isUnknown());
    }

    public function testValue()
    {
        $Humidity = new Humidity(73);

        $this->assertEquals(73, $Humidity->value());
        $this->assertEquals(69, $Humidity->set(69)->value());
        $this->assertEquals('69', $Humidity->string(false));
    }
}
