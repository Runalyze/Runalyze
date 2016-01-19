<?php

namespace Runalyze\Util;

class StringReaderTest extends \PHPUnit_Framework_TestCase
{
	public function testFindDemandedPace()
	{
		$Reader = new StringReader();

		$this->assertEquals(0, $Reader->setString('No pace here')->findDemandedPace());
		$this->assertEquals(0, $Reader->setString('Wrong pattern for 3:20')->findDemandedPace());
		$this->assertEquals(200, $Reader->setString('Wrong pattern for 3:20')->findDemandedPace(' for '));
		$this->assertEquals(200, $Reader->setString('Correct pattern in 3:20')->findDemandedPace());
		$this->assertEquals(17, $Reader->setString('Whats about 17 seconds?')->findDemandedPace('about '));
		$this->assertEquals(3600 + 23*60 + 45, $Reader->setString('And with hours in 1:23:45?')->findDemandedPace());
		$this->assertEquals(200, $Reader->setString('And multiple times in 3:20 and 4:20?')->findDemandedPace());
		$this->assertEquals(278, $Reader->setString('16 TDL [4* 4.000 in 4:38|4:27|4:14|4:01]')->findDemandedPace());
		$this->assertEquals(254, $Reader->setString('18 TDL [3* 6.000 in 4:14|4:07|4:02]')->findDemandedPace());
	}
}
