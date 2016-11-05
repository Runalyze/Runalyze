<?php

namespace Runalyze\Tests\Metrics\Common;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class UnitConversionByFactorTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testThatConversionWorksAsExpected()
    {
        $mock = $this->getMockForTrait(UnitConversionByFactorTrait::class);
        $mock->expects($this->any())
            ->method('getFactorFromBaseUnit')
            ->will($this->returnValue(1.23));

        /** @var UnitConversionByFactorTrait $mock */

        $this->assertEquals(2.46, $mock->fromBaseUnit(2.0));
        $this->assertEquals(2.0, $mock->toBaseUnit(2.46));
    }
}
