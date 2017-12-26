<?php

namespace Runalyze\Tests\Parser\Activity\Common\Filter;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;
use Runalyze\Parser\Activity\Common\Filter\NegativePauseFilter;

class NegativePauseFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var NegativePauseFilter */
    protected $Filter;

    /** @var ActivityDataContainer */
    protected $Container;

    public function setUp()
    {
        $this->Filter = new NegativePauseFilter();
        $this->Container = new ActivityDataContainer();
    }

    public function testEmptyContainer()
    {
        $this->Filter->filter($this->Container);

        $this->assertEmpty($this->Container->Pauses);
    }

    public function testExampleWithoutAnyInvalidPause()
    {
        $this->Container->Pauses->add(new Pause(117, 23));
        $this->Container->Pauses->add(new Pause(251, 19));
        $this->Container->Pauses->add(new Pause(506, 2));

        $this->Filter->filter($this->Container);

        $this->assertEquals(3, $this->Container->Pauses->count());
    }

    public function testInvalidPause()
    {
        $this->Container->Pauses->add(new Pause(117, -12));
        $this->Container->Pauses->add(new Pause(251, 0));
        $this->Container->Pauses->add(new Pause(506, 2));

        $this->Filter->filter($this->Container);

        $this->assertEquals(1, $this->Container->Pauses->count());
        $this->assertEquals([506, 2], [$this->Container->Pauses[0]->getTimeIndex(), $this->Container->Pauses[0]->getDuration()]);
    }

    public function testStrictMode()
    {
        $this->Container->Pauses->add(new Pause(117, -12));

        $this->setExpectedException(InvalidDataException::class);

        $this->Filter->filter($this->Container, true);
    }

    public function testLogMessageInNonStrictMode()
    {
        $handler = new TestHandler();
        $this->Filter->setLogger(new Logger('test', [$handler]));
        $this->Container->Pauses->add(new Pause(117, -12));

        $this->Filter->filter($this->Container);

        $this->assertTrue($handler->hasWarningThatContains('of -12s removed'));
        $this->assertEmpty($this->Container->Pauses);
    }
}
