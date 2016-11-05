<?php

namespace Runalyze\Tests\Metrics\Common;

use Runalyze\Metrics\Common\UnitConversionByDividendTrait;

class UnitConversionByDividendTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testThatConversionWorksAsExpected()
    {
        $mock = $this->getMockForTrait(UnitConversionByDividendTrait::class);
        $mock->expects($this->any())
            ->method('getDividendFromBaseUnit')
            ->will($this->returnValue(5));

        /** @var UnitConversionByDividendTrait $mock */

        $this->assertEquals(2.5, $mock->fromBaseUnit(2.0));
        $this->assertEquals(2.0, $mock->toBaseUnit(2.5));
    }
}
