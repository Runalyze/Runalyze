<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Model\Trackdata\Climb;

use Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb\ClimbProfile;

class ClimbProfileTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        new ClimbProfile();
    }

    public function testCount()
    {
        $this->assertEquals(3, count(new ClimbProfile([1.0, 1.0, 1.0], [17, 42, 31])));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThatWrongArraySizesAreCatched()
    {
        new ClimbProfile([1, 2, 3], [0]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThatSegmentsWithoutDistanceCantBeAdded()
    {
        (new ClimbProfile())->addSegment(0.0, 17);
    }

    public function testExampleProfile()
    {
        $profile = new ClimbProfile();
        $profile->addSegment(1.0, 50);
        $profile->addSegment(2.0, 80);
        $profile->addSegment(1.0, 69);
        $profile->addSegment(0.2, 30);

        $this->assertEquals(4, $profile->count());
        $this->assertEquals([1.0, 2.0, 1.0, 0.2], $profile->getDistances());
        $this->assertEquals([50, 80, 69, 30], $profile->getElevations());
        $this->assertEquals([0.05, 0.04, 0.069, 0.15], $profile->getGradients());
        $this->assertEquals([[1.0, 0.05], [2.0, 0.04], [1.0, 0.069], [0.2, 0.15]], $profile->getDistancesWithGradients());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProfileGenerationForInvalidArguments()
    {
        ClimbProfile::getClimbProfileFor([1.0, 2.0, 3.0], [10, 25]);
    }

    public function testProfileGeneration()
    {
        $profile = ClimbProfile::getClimbProfileFor(
            [0.0, 0.5, 1.0, 2.0, 5.0, 5.9, 6.1, 6.3],
            [0, 10, 20, 50, 100, 120, 130, 140],
            1.0
        );

        $this->assertEquals(5, $profile->count());
        $this->assertEquals([1.0, 1.0, 3.0, 1.1, 0.2], $profile->getDistances());
        $this->assertEquals([20, 30, 50, 30, 10], $profile->getElevations());
    }
}
