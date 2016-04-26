<?php

namespace Runalyze\Activity;

class TrainingEffectTest extends \PHPUnit_Framework_TestCase
{
    /** @expectedException \InvalidArgumentException */
	public function testInvalidValueTooSmall()
	{
        new TrainingEffect(0.9);
	}

    /** @expectedException \InvalidArgumentException */
    public function testInvalidValueTooBig()
    {
        new TrainingEffect(5.1);
    }

    /** @expectedException \InvalidArgumentException */
    public function testInvalidValueNonNumeric()
    {
        new TrainingEffect(false);
    }

    /** @expectedException \InvalidArgumentException */
    public function testInvalidValueString()
    {
        new TrainingEffect('foobar');
    }

    public function testSimpleValue()
    {
        $Effect = new TrainingEffect(3.1);

        $this->assertTrue($Effect->isKnown());
        $this->assertEquals(3.1, $Effect->value());
        $this->assertEquals(TrainingEffectLevel::IMPROVING, $Effect->level());

        $this->assertEquals(2.9, $Effect->set(2.9)->value());
        $this->assertEquals(TrainingEffectLevel::MAINTAINING, $Effect->level());

        $this->assertFalse($Effect->set(null)->isKnown());
    }

    public function testFormattingValues()
    {
        $this->assertEquals('', TrainingEffect::format(null));
        $this->assertEquals('3.1', TrainingEffect::format(3.14));
        $this->assertEquals('5.0', TrainingEffect::format(5.0));
    }

    public function testUnknownValue()
    {
        $Effect = new TrainingEffect();

        $this->assertFalse($Effect->isKnown());
        $this->assertEquals('', $Effect->string());
        $this->assertEquals('', $Effect->shortDescription());
        $this->assertEquals('', $Effect->description());
        $this->assertEquals(0, $Effect->numericValue());
        $this->assertNull($Effect->value());
        $this->assertNull($Effect->level());
    }
}
