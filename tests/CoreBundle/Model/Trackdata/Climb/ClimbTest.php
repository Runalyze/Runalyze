<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Model\Trackdata\Climb;

use Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb\Climb;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb\ClimbProfile;

class ClimbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Climb
     */
    protected function getDefaultClimb()
    {
        return new Climb(1.0, 50);
    }

    public function testConstructorWithoutIndices()
    {
        $climb = new Climb(1.0, 50);

        $this->assertEquals(1.0, $climb->getDistance());
        $this->assertEquals(50, $climb->getElevation());
        $this->assertFalse($climb->knowsAltitudeAtTop());
        $this->assertFalse($climb->knowsClimbProfile());

        $this->assertFalse($climb->knowsTrackdataStartIndex());
        $this->assertFalse($climb->knowsTrackdataEndIndex());
        $this->assertNull($climb->getTrackdataStartIndex());
        $this->assertNull($climb->getTrackdataEndIndex());
    }

    public function testConstructorWithIndices()
    {
        $climb = new Climb(1.0, 50, 42, 123);

        $this->assertTrue($climb->knowsTrackdataStartIndex());
        $this->assertTrue($climb->knowsTrackdataEndIndex());
        $this->assertEquals(42, $climb->getTrackdataStartIndex());
        $this->assertEquals(123, $climb->getTrackdataEndIndex());
    }

    public function testAltitudeAtTop()
    {
        $climb = $this->getDefaultClimb()->setAltitudeAtTop(1234);

        $this->assertTrue($climb->knowsAltitudeAtTop());
        $this->assertEquals(1234, $climb->getAltitudeAtTop());
    }

    public function testGradient()
    {
        $this->assertEquals(0.05, (new Climb(1.0, 50))->getGradient());
        $this->assertEquals(0.16, (new Climb(0.2, 32))->getGradient());
        $this->assertEquals(0.06, (new Climb(0.5, 30))->getGradient());
        $this->assertEquals(0.033, (new Climb(3.0, 100))->getGradient(), '', 0.001);
    }

    public function testClimbProfile()
    {
        $climbProfile = new ClimbProfile();
        $climb = $this->getDefaultClimb();
        $climb->setClimbProfile($climbProfile);

        $this->assertTrue($climb->knowsClimbProfile());
        $this->assertEquals($climbProfile, $climb->getClimbProfile());
    }
}
