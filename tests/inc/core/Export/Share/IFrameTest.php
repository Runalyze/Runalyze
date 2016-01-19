<?php

namespace Runalyze\Export\Share;

use Runalyze\View\Activity\FakeContext;
use Runalyze\Model\Activity;

class IFrameTest extends \PHPUnit_Framework_TestCase
{
	public function checkFeasibility()
	{
		$this->assertFalse((new IFrame(FakeContext::onlyWithActivity(
			new Activity\Entity(array(
				Activity\Entity::IS_PUBLIC => false
			))
		)))->isPossible());

		$this->assertTrue((new IFrame(FakeContext::onlyWithActivity(
			new Activity\Entity(array(
				Activity\Entity::IS_PUBLIC => true
			))
		)))->isPossible());
	}

    public function testThatCodeCanBeCreated()
    {
        ob_start();

		$Sharer = new IFrame(FakeContext::emptyContext());
		$Sharer->display();

		ob_end_clean();
    }
}
