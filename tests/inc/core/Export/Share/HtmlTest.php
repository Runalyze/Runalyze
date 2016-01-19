<?php

namespace Runalyze\Export\Share;

use Runalyze\View\Activity\FakeContext;
use Runalyze\Model\Activity;

class HtmlTest extends \PHPUnit_Framework_TestCase
{
	public function checkThatItsAlwaysPossible()
	{
		$this->assertTrue((new Html(FakeContext::onlyWithActivity(
			new Activity\Entity(array(
				Activity\Entity::IS_PUBLIC => false
			))
		)))->isPossible());

		$this->assertTrue((new Html(FakeContext::onlyWithActivity(
			new Activity\Entity(array(
				Activity\Entity::IS_PUBLIC => true
			))
		)))->isPossible());
	}

    public function testThatCodeCanBeCreated()
    {
        ob_start();

		foreach (FakeContext::examplaryContexts() as $context) {
			$Sharer = new Html($context);
			$Sharer->display();
		}

		ob_end_clean();
    }
}
