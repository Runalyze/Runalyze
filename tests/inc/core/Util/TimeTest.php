<?php

namespace Runalyze\Util;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\WeekStart;

/**
 * Test class for Time
 */
class TimeTest extends \PHPUnit_Framework_TestCase {

	protected function tearDown() {
		Configuration::General()->weekStart()->set(WeekStart::MONDAY);
	}

	public function testDiffInDays() {
		$this->assertEquals(0, Time::diffInDays(mktime(0,0,0,12,5,2000), mktime(0,0,0,12,5,2000)));
		$this->assertEquals(0, Time::diffInDays(mktime(0,0,0,12,5,2000), mktime(23,59,0,12,5,2000)));
		$this->assertEquals(1, Time::diffInDays(mktime(0,0,0,12,5,2000), mktime(0,0,0,12,6,2000)));
		$this->assertEquals(5, Time::diffInDays(mktime(0,0,0,12,5,2000), mktime(0,0,0,12,10,2000)));
		$this->assertEquals(4, Time::diffInDays(mktime(17,0,0,12,5,2000), mktime(15,0,0,12,10,2000)));
		$this->assertEquals(5, Time::diffInDays(mktime(18,0,0,12,5,2000), mktime(13,0,0,12,11,2000)));
		$this->assertEquals(365, Time::diffInDays(mktime(0,0,0,12,5,2000), mktime(0,0,0,12,5,2001)));
	}

	public function testDiffOfDays() {
		$this->assertEquals(0, Time::diffOfDates('2006-09-07', '2006-09-07'));
		$this->assertEquals(1, Time::diffOfDates('2006-09-06', '2006-09-07'));
		$this->assertEquals(2, Time::diffOfDates('2006-09-05', '2006-09-07'));
		$this->assertEquals(10, Time::diffOfDates('2006-08-28', '2006-09-07'));
		$this->assertEquals(31, Time::diffOfDates('2006-08-07', '2006-09-07'));

		$this->assertEquals(2, Time::diffOfDates('2014-03-30', '2014-04-01'));
	}

	public function testIsToday() {
		$this->assertTrue(Time::isToday(time()));
		$this->assertTrue(Time::isToday(mktime(0,0,0)));
		$this->assertTrue(Time::isToday(mktime(23,59,0)));
		$this->assertFalse(Time::isToday(mktime(-1,0,0)));
		$this->assertFalse(Time::isToday(mktime(24,10,0)));
	}

	public function testWeekstartAndWeekend() {
		Configuration::General()->weekStart()->set(WeekStart::MONDAY);

		$today = mktime(10, 22, 20, 9, 17, 2015);
		$start = mktime(0, 0, 0, 9, 14, 2015);
		$end = mktime(23, 59, 50, 9, 20, 2015);

		$this->assertEquals($start, Time::weekstart($today));
		$this->assertEquals($end, Time::weekend($today));
	}

	public function testWeekstartAndWeekendForSundaySimple() {
		Configuration::General()->weekStart()->set(WeekStart::SUNDAY);

		$today = mktime(10, 22, 20, 9, 17, 2015);
		$start = mktime(0, 0, 0, 9, 13, 2015);
		$end = mktime(23, 59, 50, 9, 19, 2015);

		$this->assertEquals($start, Time::weekstart($today));
		$this->assertEquals($end, Time::weekend($today));
	}

	public function testWeekstartAndWeekendForSundayBug() {
		Configuration::General()->weekStart()->set(WeekStart::SUNDAY);

		$today = mktime(0, 0, 0, 10, 25, 2015);
		$end = mktime(23, 59, 50, 10, 31, 2015);

		$this->assertEquals($today, Time::weekstart($today));
		$this->assertEquals($today, Time::weekstart($end));
		$this->assertEquals($end, Time::weekend($today));
		$this->assertEquals($end, Time::weekend($end));
	}

}
