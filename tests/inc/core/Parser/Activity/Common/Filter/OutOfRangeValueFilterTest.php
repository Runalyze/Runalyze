<?php

namespace Runalyze\Tests\Parser\Activity\Common\Filter;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;
use Runalyze\Parser\Activity\Common\Filter\OutOfRangeValueFilter;

class OutOfRangeValueFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var OutOfRangeValueFilter */
    protected $Filter;

    /** @var ActivityDataContainer */
    protected $Container;

    public function setUp()
    {
        $this->Filter = new OutOfRangeValueFilter();
        $this->Container = new ActivityDataContainer();
    }

    public function testEmptyContainer()
    {
        $this->Filter->filter($this->Container);
    }

    public function testExampleWithSomeOutOfRangeValues()
    {
        $this->Container->ActivityData->AvgHeartRate = 123;
        $this->Container->ActivityData->MaxHeartRate = 257;
        $this->Container->ActivityData->EnergyConsumption = 65536;
        $this->Container->ActivityData->RPE = 0;

        $handler = new TestHandler();
        $this->Filter->setLogger(new Logger('test', [$handler]));
        $this->Filter->filter($this->Container);

        $this->assertEquals(123, $this->Container->ActivityData->AvgHeartRate);
        $this->assertNull($this->Container->ActivityData->MaxHeartRate);
        $this->assertNull($this->Container->ActivityData->EnergyConsumption);
        $this->assertNull($this->Container->ActivityData->RPE);

        $this->assertTrue($handler->hasWarningThatContains('fixed'));
    }

    public function testStrictMode()
    {
        $this->Container->ActivityData->Duration = 1234567.89;

        $this->setExpectedException(InvalidDataException::class);

        $this->Filter->filter($this->Container, true);
    }
}
