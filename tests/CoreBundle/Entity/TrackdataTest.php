<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;

class TrackdataTest extends \PHPUnit_Framework_TestCase
{
    /** @var Trackdata */
    protected $Data;

    public function setUp()
    {
        $this->Data = new Trackdata();
    }

    public function testEmptyEntity()
    {
        $this->assertTrue($this->Data->isEmpty());
        $this->assertNull($this->Data->getTime());
        $this->assertNull($this->Data->getDistance());
        $this->assertInstanceOf(PauseCollection::class, $this->Data->getPauses());
        $this->assertTrue($this->Data->getPauses()->isEmpty());
    }
}
