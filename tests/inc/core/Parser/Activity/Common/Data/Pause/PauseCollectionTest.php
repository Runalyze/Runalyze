<?php

namespace Runalyze\Tests\Parser\Activity\Common\Data\Pause;

use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;

class PauseCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var PauseCollection */
    protected $Collection;

    public function setUp()
    {
        $this->Collection = new PauseCollection();
    }

    public function testConstructorWithSomeElements()
    {
        $this->Collection = new PauseCollection([
            new Pause(100, 10),
            new Pause(242, 17)
        ]);

        $this->assertEquals(2, $this->Collection->count());
        $this->assertEquals(27, $this->Collection->getTotalDuration());
    }

    public function testEmptyCollection()
    {
        $this->assertEquals(0, $this->Collection->count());
        $this->assertEquals(0, $this->Collection->getTotalDuration());
        $this->assertTrue($this->Collection->isEmpty());
        $this->assertFalse($this->Collection->offsetExists(0));
        $this->assertEmpty($this->Collection->getElements());
    }

    public function testDefaultArrayAccessors()
    {
        $this->assertEquals(0, count($this->Collection));
        $this->assertEmpty($this->Collection);

        $this->Collection[] = new Pause(100, 10);
        $this->Collection[] = new Pause(242, 17);

        $this->assertEquals(2, count($this->Collection));
        $this->assertNotEmpty($this->Collection);

        $this->assertInstanceOf(Pause::class, $this->Collection[0]);
        $this->assertEquals(242, $this->Collection[1]->getTimeIndex());
    }

    public function testAddingPauses()
    {
        $this->Collection->add(new Pause(100, 10));
        $this->Collection->add(new Pause(242, 17));

        $this->assertEquals(2, $this->Collection->count());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithBadTypes()
    {
        new PauseCollection([
            new Pause(100, 10),
            'foobar'
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetSetWithBadType()
    {
        $this->Collection[] = 'foobar';
    }

    public function testRebasing()
    {
        $this->Collection->add(new Pause(10, 1));
        $this->Collection->add(new Pause(20, 1));
        $this->Collection->add(new Pause(30, 1));
        $this->Collection->offsetUnset(1);

        $this->Collection->rebase();

        $this->assertEquals(2, $this->Collection->count());
        $this->assertEquals(10, $this->Collection[0]->getTimeIndex());
        $this->assertEquals(30, $this->Collection[1]->getTimeIndex());
    }

    public function testRebasingWithEmptyCollection()
    {
        $this->Collection->rebase();

        $this->assertEmpty($this->Collection->getElements());
    }
}
