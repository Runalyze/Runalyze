<?php

namespace Runalyze\Activity;

class GroundcontactBalanceTest extends \PHPUnit_Framework_TestCase
{

	public function testStaticFunction()
	{
		$this->assertEquals('49.5L/50.5R&nbsp;%', GroundcontactBalance::format(4950, true));
		$this->assertEquals('49.5L/50.5R', GroundcontactBalance::format(4950, false));
	}

	public function testSetter()
	{
		$Balance = new GroundcontactBalance(4950);

		$this->assertEquals(49.5, $Balance->leftInPercent());
		$this->assertEquals(50.5, $Balance->rightInPercent());

		$Balance->set(5000);
		$this->assertEquals(50.0, $Balance->leftInPercent());

		$Balance->setPercent(50.5);
		$this->assertEquals(50.5, $Balance->leftInPercent());
		$this->assertEquals(5050, $Balance->value());
	}

	public function testIsKnown()
	{
		$Balance = new GroundcontactBalance(0);
		$this->assertFalse($Balance->isKnown());

		$Balance->set(5050);
		$this->assertTrue($Balance->isKnown());
	}

}
