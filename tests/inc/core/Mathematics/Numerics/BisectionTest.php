<?php

namespace Runalyze\Tests\Mathematics\Numerics;

use Runalyze\Mathematics\Numerics\Bisection;

class BisectionTest extends \PHPUnit_Framework_TestCase
{
	public function testPerfectStartPoint()
    {
		$Bisection = new Bisection(5, 0, 10, function($x){
			return $x;
		});

		$this->assertEquals(5, $Bisection->findValue());
	}

	public function testNotEnoughIterations()
    {
		$Bisection = new Bisection(1, 0, 10, function($x){
			return $x;
		});

		$Bisection->setIterations(4);
		$Bisection->setEpsilon(0.01);

		$this->assertEquals(0.9375, $Bisection->findValue());
	}

	public function testSimpleApproximation()
    {
		$Bisection = new Bisection(1, 0, 10, function($x){
			return $x;
		});

		$Bisection->setIterations(100);
		$Bisection->setEpsilon(0.01);

		$this->assertEquals(1, $Bisection->findValue(), '', 0.01);
	}

	public function testCubicFunction()
    {
		$Bisection = new Bisection(7, -50, 20, function($x){
			return $x*$x*$x + 6;
		});

		$Bisection->setIterations(100);
		$Bisection->setEpsilon(0.01);

		$this->assertEquals(1, $Bisection->findValue(), '', 0.01);
	}
}
