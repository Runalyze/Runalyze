<?php

namespace Runalyze\Tests\Parser\Activity\Common\Filter;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;
use Runalyze\Parser\Activity\Common\Filter\NegativeTimeStepFilter;

class NegativeTimeStepFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var NegativeTimeStepFilter */
    protected $Filter;

    /** @var ActivityDataContainer */
    protected $Container;

    public function setUp()
    {
        $this->Filter = new NegativeTimeStepFilter();
        $this->Container = new ActivityDataContainer();
    }

    public function testEmptyContainer()
    {
        $this->Filter->filter($this->Container);
    }

    public function testExampleWithoutAnyInvalidTimeSteps()
    {
        $this->Container->ContinuousData->Time = [1, 2, 3, 4, 5];
        $this->Container->ContinuousData->HeartRate = [120, 122, 121, 122, 120];

        $this->Filter->filter($this->Container);

        $this->assertCount(5, $this->Container->ContinuousData->Time);
        $this->assertCount(5, $this->Container->ContinuousData->HeartRate);
    }

    public function testRemovingShortInvalidTimeSteps()
    {
        $this->Container->ContinuousData->Time = [1, 2, 1, 2, 3];
        $this->Container->ContinuousData->HeartRate = [120, 122, 99, 122, 120];

        $handler = new TestHandler();
        $this->Filter->setLogger(new Logger('test', [$handler]));
        $this->Filter->filter($this->Container);

        $this->assertEquals([1, 2, 2, 3], $this->Container->ContinuousData->Time);
        $this->assertEquals([120, 122, 122, 120], $this->Container->ContinuousData->HeartRate);

        $this->assertTrue($handler->hasWarningThatContains('of -1s removed'));
    }

    public function testStrictMode()
    {
        $this->Container->ContinuousData->Time = [1, 2, 1, 2, 3];

        $this->setExpectedException(InvalidDataException::class);

        $this->Filter->filter($this->Container, true);
    }
}
