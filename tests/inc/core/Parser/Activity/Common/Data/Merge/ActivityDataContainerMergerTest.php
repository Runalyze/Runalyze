<?php

namespace Runalyze\Tests\Parser\Activity\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Data\Merge\ActivityDataContainerMerger;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Round\Round;

class ActivityDataContainerMergerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ActivityDataContainer */
    protected $FirstContainer;

    /** @var ActivityDataContainer */
    protected $SecondContainer;

    public function setUp()
    {
        $this->FirstContainer = new ActivityDataContainer();
        $this->SecondContainer = new ActivityDataContainer();
    }

    public function testThatMergeWorksWithEmptyObjects()
    {
        new ActivityDataContainerMerger($this->FirstContainer, $this->SecondContainer);
    }

    public function testThatObjectsAreCloned()
    {
        $merger = new ActivityDataContainerMerger($this->FirstContainer, $this->SecondContainer);
        $newContainer = $merger->getResultingContainer();

        $this->assertNotSame($newContainer, $this->FirstContainer);
        $this->assertNotSame($newContainer->Metadata, $this->FirstContainer->Metadata);
        $this->assertNotSame($newContainer->ActivityData, $this->FirstContainer->ActivityData);
        $this->assertNotSame($newContainer->ContinuousData, $this->FirstContainer->ContinuousData);
        $this->assertNotSame($newContainer->WeatherData, $this->FirstContainer->WeatherData);
        $this->assertNotSame($newContainer->FitDetails, $this->FirstContainer->FitDetails);
        $this->assertNotSame($newContainer->Rounds, $this->FirstContainer->Rounds);
        $this->assertNotSame($newContainer->Pauses, $this->FirstContainer->Pauses);
    }

    public function testMergingPausesAndRounds()
    {
        $this->SecondContainer->Rounds->add(new Round(1.0, 317));
        $this->SecondContainer->Rounds->add(new Round(1.0, 286));
        $this->SecondContainer->Pauses->add(new Pause(317, 21));

        $merger = new ActivityDataContainerMerger($this->FirstContainer, $this->SecondContainer);
        $newContainer = $merger->getResultingContainer();

        $this->assertEquals(2, $newContainer->Rounds->count());
        $this->assertEquals(1, $newContainer->Pauses->count());
    }

    public function testMergingOfRRIntervals()
    {
        $this->SecondContainer->RRIntervals = [620, 650, 630, 642];

        $merger = new ActivityDataContainerMerger($this->FirstContainer, $this->SecondContainer);
        $newContainer = $merger->getResultingContainer();

        $this->assertEquals([620, 650, 630, 642], $newContainer->RRIntervals);
    }
}
