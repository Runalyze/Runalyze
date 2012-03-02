<?php
require_once '../inc/class.Validator.php';

class Test_Validator extends PHPUnit_Framework_TestCase {
	/**
	 * Test: Validator::dateToTimestamp($dateAsString, $default = 0)
	 */
	public function test_dateToTimestamp() {
		$this->assertEquals(mktime(0,0,0,1,1,2000), Validator::dateToTimestamp('1.1.2000'));
		$this->assertEquals(mktime(0,0,0,7,1,2000), Validator::dateToTimestamp('1.7.2000'));
		$this->assertEquals(mktime(0,0,0,12,31,2012), Validator::dateToTimestamp('31.12.2012'));
		$this->assertEquals(mktime(0,0,0,9,13,date('Y')), Validator::dateToTimestamp('13.9'));
	}

	public function test_dateToTimestamp_wrongInput() {
		$this->assertEquals(0, Validator::dateToTimestamp(''));
		$this->assertEquals(0, Validator::dateToTimestamp('17'));
		$this->assertEquals(0, Validator::dateToTimestamp('1.1.2000.1'));
	}

	public function test_dateToTimestamp_defaultValue() {
		$this->assertEquals(time(), Validator::dateToTimestamp('', time()));
	}

	/**
	 * Test: Validator::isInRange($low, $high, $value)
	 */
	public function test_isInRange() {
		$this->assertEquals(false, Validator::isInRange(0,100, -1));
		$this->assertEquals(true, Validator::isInRange(0,100, 0));
		$this->assertEquals(true, Validator::isInRange(0,100, 57));
		$this->assertEquals(true, Validator::isInRange(0,100, 100));
		$this->assertEquals(false, Validator::isInRange(0,100, 101));
	}
}
?>