<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Configuration\Category;

use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\ActivityForm;

class ActivityFormTest extends \PHPUnit_Framework_TestCase
{
    public function testIgnoredActivityIds()
    {
        $config = new ActivityForm();

        $this->assertCount(0, $config->getIgnoredActivityIds());

        $config->set('GARMIN_IGNORE_IDS', '2013-02-28T08:29:54Z,2013-02-26T16:48:35Z,2013-03-30T10:20:16Z,2013-07-16T15:42:46Z,2013-07-16T15:25:43Z,2013-08-31T12:18:27Z,2014-01-09T19:08:46Z,2014-03-06T16:30:40Z,2014-03-18T17:56:55Z,2014-05-20T14:17:41Z,');

        $this->assertCount(10, $config->getIgnoredActivityIds());
    }
}
