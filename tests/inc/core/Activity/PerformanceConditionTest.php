<?php

namespace Runalyze\Activity;

class PerformanceConditionTest extends \PHPUnit_Framework_TestCase
{

    public function testFormattingValues()
    {
        $this->assertEquals('', PerformanceCondition::format(null));
        $this->assertEquals('+2', PerformanceCondition::format(102));
        $this->assertEquals('&plusmn;0', PerformanceCondition::format(100));
        $this->assertEquals('-3', PerformanceCondition::format(97));
    }

    public function testUnknownValue()
    {
        $Condition = new PerformanceCondition();

        $this->assertFalse($Condition->isKnown());
        $this->assertEquals('', $Condition->string());
        $this->assertNull($Condition->value());
    }

    public function testThatInvalidValuesAreTreatedAsUnknown()
    {
        $Condition = new PerformanceCondition();

        $this->assertNull($Condition->set(PerformanceCondition::LOWER_LIMIT - 1)->value());
        $this->assertNotNull($Condition->set(PerformanceCondition::LOWER_LIMIT)->value());
        $this->assertNotNull($Condition->set(PerformanceCondition::UPPER_LIMIT)->value());
        $this->assertNull($Condition->set(PerformanceCondition::UPPER_LIMIT + 1)->value());
    }
}
