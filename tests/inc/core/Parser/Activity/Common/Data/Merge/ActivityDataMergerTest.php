<?php

namespace Runalyze\Tests\Parser\Activity\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\ActivityData;
use Runalyze\Parser\Activity\Common\Data\Merge\ActivityDataMerger;

class ActivityDataMergerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ActivityData */
    protected $FirstData;

    /** @var ActivityData */
    protected $SecondData;

    public function setUp()
    {
        $this->FirstData = new ActivityData();
        $this->SecondData = new ActivityData();
    }

    public function testThatMergeWorksWithEmptyObjects()
    {
        (new ActivityDataMerger($this->FirstData, $this->SecondData))->merge();
    }

    public function testWithEmptySecondObject()
    {
        $this->FirstData->Duration = 321;

        (new ActivityDataMerger($this->FirstData, $this->SecondData))->merge();

        $this->assertEquals(321, $this->FirstData->Duration);
    }

    public function testWithEmptyFirstObject()
    {
        $this->FirstData->Duration = 321;

        (new ActivityDataMerger($this->FirstData, $this->SecondData))->merge();

        $this->assertEquals(321, $this->FirstData->Duration);
    }
}
