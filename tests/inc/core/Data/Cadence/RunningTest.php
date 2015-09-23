<?php

namespace Runalyze\Data\Cadence;

class RunningTest extends \PHPUnit_Framework_TestCase {

	public function testValue() {
		$Cadence = new Running(90);

		$this->assertEquals(180, $Cadence->value());
	}

	public function testFactor() {
		$Cadence = new Running();

		$this->assertEquals(160, $Cadence->useFactor(80));
	}

	public function testManipulatingArray() {
		$array = array(70, 80, 90, 100, 105);
		$Cadence = new Running();
		$Cadence->manipulateArray($array);

		$this->assertEquals(array(140, 160, 180, 200, 210), $array);
	}

}
