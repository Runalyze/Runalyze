<?php

namespace Runalyze\Tests\Metrics\Common;

use Runalyze\Metrics\Common\BaseUnitTrait;

class BaseUnitTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testThatConversionWorksAsExpected()
    {
        /** @var BaseUnitTrait $mock */
        $mock = $this->getMockForTrait(BaseUnitTrait::class);

        $this->assertEquals(3.14, $mock->fromBaseUnit(3.14));
        $this->assertEquals(42.195, $mock->toBaseUnit(42.195));
    }
}
