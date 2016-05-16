<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

class AbstractStrategy_MockTester extends AbstractStrategy
{
	public function canHandleData()
	{
		return true;
	}

	public function correctElevation()
	{
		$this->ElevationPoints = array(1,2,3);
	}
}

class AbstractStrategyTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testEmptyArrays()
	{
		new AbstractStrategy_MockTester(array(), array());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testDifferentArraySizes()
	{
		new AbstractStrategy_MockTester(array(1,2,3), array(1,2,3,4));
	}

	public function testCorrectConstruction()
	{
		new AbstractStrategy_MockTester(array(1,2,3), array(1,2,3));
	}

	public function testGetCorrectedElevation()
	{
		$Strategy = new AbstractStrategy_MockTester(array(1,2,3), array(1,2,3));
		$Strategy->correctElevation();

		$this->assertEquals(array(1,2,3), $Strategy->getCorrectedElevation());
	}

}
