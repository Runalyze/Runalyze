<?php

namespace Runalyze\Tests\Metrics\Common\Unit;

use Runalyze\Metrics\Common\Unit\None;

class NoneTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new None();

        $this->assertEquals(1.23, $unit->fromBaseUnit(1.23));
        $this->assertEquals(3.14, $unit->toBaseUnit(3.14));
        $this->assertEquals('', $unit->getAppendix());
    }
}
