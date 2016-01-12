<?php

namespace Runalyze\Data\Weather;

class PressureTest extends \PHPUnit_Framework_TestCase
{
    /** @expectedException \InvalidArgumentException */
    public function testNonNumericValue()
    {
        new Pressure('foobar');
    }

    public function testNull()
    {
        $Pressure = new Pressure();

        $this->assertEquals(null, $Pressure->value());
        $this->assertEquals('', $Pressure->string());
        $this->assertTrue($Pressure->isUnknown());
    }

    public function testValue()
    {
        $Pressure = new Pressure(1090);

        $this->assertEquals(1090, $Pressure->value());
        $this->assertEquals(1063, $Pressure->set(1063)->value());
        $this->assertEquals('1063', $Pressure->string(false));
    }
}
