<?php

class HelperTest extends PHPUnit_Framework_TestCase
{
	public function testArrayTrim()
	{
		$this->assertEquals( array('a', 'b', 'c', 'd', 1), Helper::arrayTrim(array(' a', 'b ', ' c ', 'd', 1)) );
	}

	public function testFloorFor()
	{
		$this->assertEquals( 0, Helper::floorFor(4, 5) );
		$this->assertEquals( 0, Helper::floorFor(9, 20) );
		$this->assertEquals( 0, Helper::floorFor(11, 20) );
		$this->assertEquals( 20, Helper::floorFor(20, 20) );
		$this->assertEquals( 20, Helper::floorFor(21, 20) );
		$this->assertEquals( 20, Helper::floorFor(29, 20) );
	}

	public function testCeilFor()
	{
		$this->assertEquals( 5, Helper::ceilFor(4, 5) );
		$this->assertEquals( 20, Helper::ceilFor(9, 20) );
		$this->assertEquals( 20, Helper::ceilFor(11, 20) );
		$this->assertEquals( 20, Helper::ceilFor(20, 20) );
		$this->assertEquals( 40, Helper::ceilFor(21, 20) );
		$this->assertEquals( 40, Helper::ceilFor(29, 20) );
	}

	public function testTwoNumbers()
	{
		$this->assertEquals( '00', Helper::TwoNumbers(0) );
		$this->assertEquals( '05', Helper::TwoNumbers(5) );
		$this->assertEquals( '09', Helper::TwoNumbers(9) );
		$this->assertEquals( '10', Helper::TwoNumbers(10) );
		$this->assertEquals( '17', Helper::TwoNumbers(17) );
	}

	public function testUnknown()
	{
		$this->assertEquals( '?', Helper::Unknown(null) );
		$this->assertEquals( '-', Helper::Unknown(0, '-') );
		$this->assertEquals( 1, Helper::Unknown(1, '-') );
		$this->assertEquals( 'test', Helper::Unknown('test', '-') );
	}

	public function testCommaToPoint()
	{
		$this->assertEquals( '17.5', Helper::CommaToPoint('17.5') );
		$this->assertEquals( '17.5', Helper::CommaToPoint('17,5') );
		$this->assertEquals( '175', Helper::CommaToPoint('175') );
	}
}
