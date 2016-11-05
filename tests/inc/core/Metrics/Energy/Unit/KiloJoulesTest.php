<?php

namespace Runalyze\Tests\Metrics\Energy\Unit;

use Runalyze\Metrics\Energy\Unit\Kilojoules;

class KiloJoulesTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Kilojoules();

        $this->assertEquals(419, $unit->fromBaseUnit(100), '', 0.5);
        $this->assertEquals(100, $unit->toBaseUnit(419), '', 0.5);

        $this->assertEquals(100, $unit->fromBaseUnit(24), '', 0.5);
        $this->assertEquals(24, $unit->toBaseUnit(100), '', 0.5);
    }
}
