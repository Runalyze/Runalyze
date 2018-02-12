<?php

namespace Runalyze\Tests\Parser\Activity\Common\Filter;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;
use Runalyze\Parser\Activity\Common\Filter\NonMatchingArraySizeFilter;

class NonMatchingArraySizeFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var NonMatchingArraySizeFilter */
    protected $Filter;

    /** @var ActivityDataContainer */
    protected $Container;

    public function setUp()
    {
        $this->Filter = new NonMatchingArraySizeFilter();
        $this->Container = new ActivityDataContainer();
    }

    public function testEmptyContainer()
    {
        $this->Filter->filter($this->Container);
    }

    public function testExampleWithoutAnyNonMatchingArraySizes()
    {
        $this->Container->ContinuousData->Time = [300, 600, 900, 1200, 1500];
        $this->Container->ContinuousData->Distance = [1.0, 2.0, 3.0, 4.0, 5.0];
        $this->Container->ContinuousData->HeartRate = [120, 122, 121, 122, 125];

        $this->Filter->filter($this->Container);

        $this->assertCount(5, $this->Container->ContinuousData->Time);
        $this->assertCount(5, $this->Container->ContinuousData->Distance);
        $this->assertCount(5, $this->Container->ContinuousData->HeartRate);
    }

    public function testRecoverableNonMatchingArraySize()
    {
        $this->Container->ContinuousData->Time = [300, 600, 900, 1200];
        $this->Container->ContinuousData->Distance = [1.0, 2.0, 3.0, 4.0, 5.0];
        $this->Container->ContinuousData->HeartRate = [120, 122, 121, 122, 125];

        $handler = new TestHandler();
        $this->Filter->setLogger(new Logger('test', [$handler]));
        $this->Filter->filter($this->Container);

        $this->assertEquals([300, 600, 900, 1200], $this->Container->ContinuousData->Time);
        $this->assertEquals([1.0, 2.0, 3.0, 4.0], $this->Container->ContinuousData->Distance);
        $this->assertEquals([120, 122, 121, 122], $this->Container->ContinuousData->HeartRate);

        $this->assertTrue($handler->hasWarningThatContains('fixed'));
    }

    public function testStrictMode()
    {
        $this->Container->ContinuousData->Time = [300, 600, 900, 1200];
        $this->Container->ContinuousData->Distance = [1.0, 2.0, 3.0, 4.0, 5.0];

        $this->setExpectedException(InvalidDataException::class);

        $this->Filter->filter($this->Container, true);
    }

    public function testUnrecoverableSizeMismatch()
    {
        $this->Container->ContinuousData->Time = range(0.0, 314.0, 1.0);
        $this->Container->ContinuousData->Distance = range(0.01, 0.27, 0.01);

        $this->setExpectedException(InvalidDataException::class);

        $this->Filter->filter($this->Container, false);
    }
}
