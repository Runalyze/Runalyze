<?php

namespace Runalyze\Activity;

class TrainingEffectLevelTest extends \PHPUnit_Framework_TestCase
{
    /** @expectedException \InvalidArgumentException */
	public function testInvalidLevelTooSmall()
	{
		TrainingEffectLevel::levelFor(0.9);
	}

    /** @expectedException \InvalidArgumentException */
    public function testInvalidLevelTooBig()
    {
        TrainingEffectLevel::levelFor(5.1);
    }

    /** @expectedException \InvalidArgumentException */
    public function testInvalidLevelNonNumeric()
    {
        TrainingEffectLevel::levelFor(false);
    }

    /** @expectedException \InvalidArgumentException */
    public function testInvalidLevelString()
    {
        TrainingEffectLevel::levelFor('foobar');
    }

    public function testValidLevels()
    {
        $this->assertEquals(TrainingEffectLevel::EASY, TrainingEffectLevel::levelFor(1.0));
        $this->assertEquals(TrainingEffectLevel::EASY, TrainingEffectLevel::levelFor(1.9));
        $this->assertEquals(TrainingEffectLevel::MAINTAINING, TrainingEffectLevel::levelFor(2.0));
        $this->assertEquals(TrainingEffectLevel::MAINTAINING, TrainingEffectLevel::levelFor(2.9));
        $this->assertEquals(TrainingEffectLevel::IMPROVING, TrainingEffectLevel::levelFor(3.0));
        $this->assertEquals(TrainingEffectLevel::IMPROVING, TrainingEffectLevel::levelFor(3.9));
        $this->assertEquals(TrainingEffectLevel::HIGHLY_IMPROVING, TrainingEffectLevel::levelFor(4.0));
        $this->assertEquals(TrainingEffectLevel::HIGHLY_IMPROVING, TrainingEffectLevel::levelFor(4.9));
        $this->assertEquals(TrainingEffectLevel::OVERREACHING, TrainingEffectLevel::levelFor(5.0));
    }

    /** @expectedException \InvalidArgumentException */
    public function testLabelForInvalidLevel()
    {
        TrainingEffectLevel::label(0);
    }

    /** @expectedException \InvalidArgumentException */
    public function testDescriptionForInvalidLevel()
    {
        TrainingEffectLevel::description(TrainingEffectLevel::OVERREACHING + 1);
    }

    public function testThatLabelAndDescriptionAreDefinedForAllLevels()
    {
        foreach (TrainingEffectLevel::getEnum() as $level) {
            $this->assertNotEmpty(TrainingEffectLevel::label($level));
            $this->assertNotEmpty(TrainingEffectLevel::description($level));
        }
    }
}
