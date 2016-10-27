<?php

use Runalyze\Data\Weather\HeatIndexEffect;

class HeatIndexEffectTest extends \PHPUnit_Framework_TestCase
{
	public function testDescription()
	{
		foreach (HeatIndexEffect::getEnum() as $level) {
			HeatIndexEffect::description($level);
		}
	}
	
	public function testLabel()
	{
		foreach (HeatIndexEffect::getEnum() as $level) {
			HeatIndexEffect::label($level);
		}
	}
	
	public function testIcon()
	{
		foreach (HeatIndexEffect::getEnum() as $level) {
			HeatIndexEffect::icon($level);
		}
	}
	
	public function testLevelFor()
	{
		$this->assertEquals(1, HeatIndexEffect::levelFor(90));
		$this->assertEquals(HeatIndexEffect::EXTREME_DANGER, HeatIndexEffect::levelFor(140));
	}
	
	public function testColorFor()
	{
		$this->assertEquals('#ffa500', HeatIndexEffect::ColorFor(HeatIndexEffect::DANGER));

	}
	
}
