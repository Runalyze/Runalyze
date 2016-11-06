<?php

use Runalyze\Data\Weather\HeatIndexEffect;

class HeatIndexEffectTest extends \PHPUnit_Framework_TestCase
{
	public function testThatDescriptionCanBeCalledForAllLevels()
	{
		foreach (HeatIndexEffect::getEnum() as $level) {
			HeatIndexEffect::description($level);
		}
	}

	public function testThatLabelCanBeCalledForAllLevels()
	{
		foreach (HeatIndexEffect::getEnum() as $level) {
			HeatIndexEffect::label($level);
		}
	}

	public function testThatIconCanBeCalledForAllLevels()
	{
		foreach (HeatIndexEffect::getEnum() as $level) {
			HeatIndexEffect::icon($level);
		}
	}

	public function testSomeLevels()
	{
		$this->assertEquals(HeatIndexEffect::NO_EFFECT, HeatIndexEffect::levelFor(79));
		$this->assertEquals(HeatIndexEffect::CAUTION, HeatIndexEffect::levelFor(90));
		$this->assertEquals(HeatIndexEffect::EXTREME_CAUTION, HeatIndexEffect::levelFor(91));
		$this->assertEquals(HeatIndexEffect::DANGER, HeatIndexEffect::levelFor(105));
		$this->assertEquals(HeatIndexEffect::EXTREME_DANGER, HeatIndexEffect::levelFor(140));
	}
}
