<?php

namespace Runalyze\View\Leaflet;

use Runalyze\Model\Trackdata\Entity;
use Runalyze\Model\Trackdata\Pause;
use Runalyze\View\Activity\FakeContext;

class ActivityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1805
     */
	public function testThatMapWorksWithPausesButWithoutTimeArray()
    {
		$Context = FakeContext::outdoorContext();
        $Context->trackdata()->set(Entity::TIME, []);
        $Context->trackdata()->pauses()->add(new Pause(123, 10));

        new Activity('test', $Context->route(), $Context->trackdata(), true);
	}

}
