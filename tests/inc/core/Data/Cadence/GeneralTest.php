<?php

namespace Runalyze\Data\Cadence;

class GeneralTest extends \PHPUnit_Framework_TestCase {

	public function testValue() {
		$Cadence = new General(90);

		$this->assertEquals(90, $Cadence->value());
	}

}
