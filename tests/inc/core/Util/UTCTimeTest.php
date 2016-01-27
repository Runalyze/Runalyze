<?php

namespace Runalyze\Util;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\WeekStart;

/**
 * Test class for Time
 */
class UTCTimeTest extends \PHPUnit_Framework_TestCase {

	public function testGetCurrentUTCTime() {
		date_default_timezone_set('Europe/Berlin');
		$this->assertEquals((new UTCTime)->getTimestamp(),time());
		$this->assertEquals((new UTCTime)->getTimezone()->getName(),'UTC');

	}

}
