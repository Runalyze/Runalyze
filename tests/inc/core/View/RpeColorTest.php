<?php

namespace Runalyze\Tests\View;

use Runalyze\View\RpeColor;

class RpeColorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyObject()
    {
        $object = new RpeColor();

        $this->assertNull($object->value());
        $this->assertEquals('transparent', $object->borderColor());
    }

    public function testSetting()
    {
        $viaConstructor = new RpeColor(17);
        $this->assertEquals(17, $viaConstructor->value());

        $viaSet = new RpeColor();
        $this->assertEquals(19, $viaSet->setValue(19)->value());
    }

	public function testBackgroundColor()
    {
        $rpe = new RpeColor();

        $this->assertEquals('transparent', $rpe->setValue(0)->borderColor());
        $this->assertEquals('#225ea8', $rpe->setValue(6)->borderColor());
        $this->assertEquals('#41b6c4', $rpe->setValue(9)->borderColor());
        $this->assertEquals('#a1dab4', $rpe->setValue(12)->borderColor());
        $this->assertEquals('#fecc5c', $rpe->setValue(14)->borderColor());
        $this->assertEquals('#fd8d3c', $rpe->setValue(18)->borderColor());
        $this->assertEquals('#e31a1c', $rpe->setValue(20)->borderColor());
    }
}
