<?php

namespace Runalyze\Tests\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Filter\InvalidRRIntervalFilter;

class InvalidRRIntervalFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var InvalidRRIntervalFilter */
    protected $Filter;

    /** @var ActivityDataContainer */
    protected $Container;

    public function setUp()
    {
        $this->Filter = new InvalidRRIntervalFilter();
        $this->Container = new ActivityDataContainer();
    }

    public function testEmptyContainer()
    {
        $this->Filter->filter($this->Container);

        $this->assertEquals([], $this->Container->RRIntervals);
    }

    public function testSimpleExample()
    {
        $this->Container->RRIntervals = [723, 751, 0, 739, 760, 0, 747];

        $this->Filter->filter($this->Container);

        $this->assertEquals([723, 751, 739, 760, 747], $this->Container->RRIntervals);
    }

    public function testOnlyZeros()
    {
        $this->Container->RRIntervals = [0, 0, 0];

        $this->Filter->filter($this->Container);

        $this->assertEquals([], $this->Container->RRIntervals);
    }
}
