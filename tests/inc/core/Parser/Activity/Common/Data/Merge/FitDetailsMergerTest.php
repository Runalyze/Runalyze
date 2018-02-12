<?php

namespace Runalyze\Tests\Parser\Activity\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\FitDetails;
use Runalyze\Parser\Activity\Common\Data\Merge\FitDetailsMerger;

class FitDetailsMergerTest extends \PHPUnit_Framework_TestCase
{
    /** @var FitDetails */
    protected $FirstDetails;

    /** @var FitDetails */
    protected $SecondDetails;

    public function setUp()
    {
        $this->FirstDetails = new FitDetails();
        $this->SecondDetails = new FitDetails();
    }

    public function testThatMergeWorksWithEmptyObjects()
    {
        (new FitDetailsMerger($this->FirstDetails, $this->SecondDetails))->merge();
    }

    public function testMergingPerformanceCondition()
    {
        $this->FirstDetails->PerformanceCondition = 3;
        $this->SecondDetails->PerformanceConditionEnd = -1;

        (new FitDetailsMerger($this->FirstDetails, $this->SecondDetails))->merge();

        $this->assertEquals(3, $this->FirstDetails->PerformanceCondition);
        $this->assertEquals(-1, $this->FirstDetails->PerformanceConditionEnd);
    }
}
