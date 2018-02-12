<?php

namespace Runalyze\Tests\Parser\Activity\Common\Filter;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;
use Runalyze\Parser\Activity\Common\Filter\NegativeDistanceStepFilter;

class NegativeDistanceStepFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var NegativeDistanceStepFilter */
    protected $Filter;

    /** @var ActivityDataContainer */
    protected $Container;

    public function setUp()
    {
        $this->Filter = new NegativeDistanceStepFilter();
        $this->Container = new ActivityDataContainer();
    }

    public function testEmptyContainer()
    {
        $this->Filter->filter($this->Container);
    }

    public function testExampleWithoutAnyInvalidDistanceSteps()
    {
        $this->Container->ContinuousData->Distance = [1.0, 2.0, 3.0, 4.0, 5.0];

        $this->Filter->filter($this->Container);

        $this->assertCount(5, $this->Container->ContinuousData->Distance);
    }

    public function testFixMissingDistanceStep()
    {
        $this->Container->ContinuousData->Distance = [1.0, 2.0, 0.0, 4.0, 5.0];

        $handler = new TestHandler();
        $this->Filter->setLogger(new Logger('test', [$handler]));
        $this->Filter->filter($this->Container);

        $this->assertEquals([1.0, 2.0, 2.0, 4.0, 5.0], $this->Container->ContinuousData->Distance);

        $this->assertTrue($handler->hasWarningThatContains('fixed'));
    }

    public function testStrictMode()
    {
        $this->Container->ContinuousData->Distance = [1.0, 2.0, 0.0, 4.0, 5.0];

        $this->setExpectedException(InvalidDataException::class);

        $this->Filter->filter($this->Container, true);
    }

    public function testUnrecoverableDistanceStep()
    {
        $this->Container->ContinuousData->Distance = [1.0, 2.0, 1.4, 1.6, 4.0, 5.0];

        $this->setExpectedException(InvalidDataException::class);

        $this->Filter->filter($this->Container, false);
    }
}
