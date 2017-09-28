<?php

namespace Runalyze\Tests\Parser\Activity\Data;

use Runalyze\Parser\Activity\Common\Data\ActivityData;
use Runalyze\Parser\Activity\Common\Data\ContinuousData;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;

class ActivityDataTest extends \PHPUnit_Framework_TestCase
{
    /** @var ActivityData */
    protected $Data;

    public function setUp()
    {
        $this->Data = new ActivityData();
    }

    public function testCompletionFromEmptyContinuousData()
    {
        $this->Data->completeFromContinuousData(new ContinuousData());

        $this->assertNull($this->Data->Duration);
        $this->assertNull($this->Data->Distance);
        $this->assertNull($this->Data->AvgPower);
        $this->assertNull($this->Data->MaxPower);
        $this->assertNull($this->Data->AvgHeartRate);
        $this->assertNull($this->Data->MaxHeartRate);
        $this->assertNull($this->Data->AvgCadence);
        $this->assertNull($this->Data->AvgGroundContactTime);
        $this->assertNull($this->Data->AvgVerticalOscillation);
        $this->assertNull($this->Data->AvgGroundContactBalance);
    }

    public function testCompletionFromTimeInContinuousDataOnly()
    {
        $continuousData = new ContinuousData();
        $continuousData->Time = range(0, 10);

        $this->Data->completeFromContinuousData($continuousData);

        $this->assertEquals(10, $this->Data->Duration);
        $this->assertNull($this->Data->Distance);
        $this->assertNull($this->Data->AvgPower);
        $this->assertNull($this->Data->MaxPower);
        $this->assertNull($this->Data->AvgHeartRate);
        $this->assertNull($this->Data->MaxHeartRate);
        $this->assertNull($this->Data->AvgCadence);
        $this->assertNull($this->Data->AvgGroundContactTime);
        $this->assertNull($this->Data->AvgVerticalOscillation);
        $this->assertNull($this->Data->AvgGroundContactBalance);
    }

    protected function getExampleForFilledContinuousData()
    {
        $continuousData = new ContinuousData();
        $continuousData->Time = range(0, 10);
        $continuousData->Distance = range(0, 0.05, 0.005);
        $continuousData->Power = [0, 100, 100, 100, 100, 100, 100, 100, 100, 50, 150];
        $continuousData->HeartRate = [0, 120, 120, 120, 120, 120, 120, 120, 120, 100, 140];
        $continuousData->Cadence = [0, 90, 90, 90, 90, 90, 90, 90, 90, 100, 80];
        $continuousData->GroundContactTime = [0, 250, 240, 230, 240, 250, 260, 270, 260, 250, 250];
        $continuousData->VerticalOscillation = [0, 78, 80, 82, 84, 86, 88, 90, 92, 94, 96];
        $continuousData->GroundContactBalance = [0, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000];

        return $continuousData;
    }

    public function testCompletionFromContinuousData()
    {
        $this->Data->completeFromContinuousData($this->getExampleForFilledContinuousData());

        $this->assertEquals(10, $this->Data->Duration);
        $this->assertEquals(0.05, $this->Data->Distance);
        $this->assertEquals(100, $this->Data->AvgPower);
        $this->assertEquals(150, $this->Data->MaxPower);
        $this->assertEquals(120, $this->Data->AvgHeartRate);
        $this->assertEquals(140, $this->Data->MaxHeartRate);
        $this->assertEquals(90, $this->Data->AvgCadence);
        $this->assertEquals(250, $this->Data->AvgGroundContactTime);
        $this->assertEquals(87, $this->Data->AvgVerticalOscillation);
        $this->assertEquals(5000, $this->Data->AvgGroundContactBalance);
    }

    public function testCompletionFromContinuousDataDoesNotOverwrite()
    {
        $this->Data->Duration = 120;
        $this->Data->Distance = 0.5;
        $this->Data->AvgPower = 320;
        $this->Data->MaxPower = 475;
        $this->Data->AvgHeartRate = 169;
        $this->Data->MaxHeartRate = 201;
        $this->Data->AvgCadence = 92;
        $this->Data->AvgGroundContactTime = 189;
        $this->Data->AvgVerticalOscillation = 76;
        $this->Data->AvgGroundContactBalance = 5015;

        $this->Data->completeFromContinuousData($this->getExampleForFilledContinuousData());

        $this->assertEquals(120, $this->Data->Duration);
        $this->assertEquals(0.5, $this->Data->Distance);
        $this->assertEquals(320, $this->Data->AvgPower);
        $this->assertEquals(475, $this->Data->MaxPower);
        $this->assertEquals(169, $this->Data->AvgHeartRate);
        $this->assertEquals(201, $this->Data->MaxHeartRate);
        $this->assertEquals(92, $this->Data->AvgCadence);
        $this->assertEquals(189, $this->Data->AvgGroundContactTime);
        $this->assertEquals(76, $this->Data->AvgVerticalOscillation);
        $this->assertEquals(5015, $this->Data->AvgGroundContactBalance);
    }

    public function testCompletionFromEmptyPauses()
    {
        $this->Data->Duration = 123;
        $this->Data->completeFromPauses(new PauseCollection());

        $this->assertEquals(123, $this->Data->ElapsedTime);
    }

    public function testCompletionFromEmptyPausesWithNoDuration()
    {
        $this->Data->completeFromPauses(new PauseCollection());

        $this->assertNull($this->Data->ElapsedTime);
    }

    public function testCompletionFromPauses()
    {
        $this->Data->Duration = 123;
        $this->Data->completeFromPauses(new PauseCollection([
            new Pause(20, 17),
            new Pause(112, 42)
        ]));

        $this->assertEquals(182, $this->Data->ElapsedTime);
    }

    public function testThatPausesCanNotOverwrite()
    {
        $this->Data->Duration = 123;
        $this->Data->ElapsedTime = 125;
        $this->Data->completeFromPauses(new PauseCollection([
            new Pause(17, 3)
        ]));

        $this->assertEquals(125, $this->Data->ElapsedTime);
    }

    public function testCompletionFromEmptyRounds()
    {
        $this->Data->completeFromRounds(new RoundCollection());

        $this->assertNull($this->Data->Duration);
        $this->assertNull($this->Data->Distance);
    }

    public function testCompletionFromRounds()
    {
        $this->Data->completeFromRounds(new RoundCollection([
            new Round(0.4, 61),
            new Round(0.4, 68),
            new Round(0.2, 32)
        ]));

        $this->assertEquals(161, $this->Data->Duration);
        $this->assertEquals(1.0, $this->Data->Distance);
    }

    public function testThatRoundsCanNotOverwrite()
    {
        $this->Data->Duration = 127;
        $this->Data->Distance = 0.8;
        $this->Data->completeFromRounds(new RoundCollection([
            new Round(0.2, 32)
        ]));

        $this->assertEquals(127, $this->Data->Duration);
        $this->assertEquals(0.8, $this->Data->Distance);
    }
}
