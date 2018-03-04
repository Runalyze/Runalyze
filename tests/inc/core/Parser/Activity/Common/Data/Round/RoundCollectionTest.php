<?php

namespace Runalyze\Tests\Parser\Activity\Common\Data\Round;

use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;

class RoundCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var RoundCollection */
    protected $Collection;

    public function setUp()
    {
        $this->Collection = new RoundCollection();
    }

    public function testConstructorWithSomeElements()
    {
        $this->Collection = new RoundCollection([
            new Round(1.0, 285),
            new Round(1.0, 260)
        ]);

        $this->assertEquals(2, $this->Collection->count());
        $this->assertEquals(2.0, $this->Collection->getTotalDistance());
        $this->assertEquals(545, $this->Collection->getTotalDuration());
    }

    public function testDefaultConstructor()
    {
        $this->assertEquals(0, $this->Collection->count());
        $this->assertEquals(0.0, $this->Collection->getTotalDistance());
        $this->assertEquals(0, $this->Collection->getTotalDuration());
        $this->assertTrue($this->Collection->isEmpty());
        $this->assertFalse($this->Collection->offsetExists(0));
        $this->assertEmpty($this->Collection->getElements());
    }

    public function testDefaultArrayAccessors()
    {
        $this->assertEquals(0, count($this->Collection));
        $this->assertEmpty($this->Collection);

        $this->Collection[] = new Round(1.0, 285);
        $this->Collection[] = new Round(1.0, 260);

        $this->assertEquals(2, count($this->Collection));
        $this->assertNotEmpty($this->Collection);

        $this->assertInstanceOf(Round::class, $this->Collection[0]);
        $this->assertEquals(260, $this->Collection[1]->getDuration());
    }

    public function testAddingRounds()
    {
        $this->Collection->add(new Round(1.0, 285));
        $this->Collection->add(new Round(1.0, 260));

        $this->assertEquals(2, $this->Collection->count());
    }

    public function testRoundingDurations()
    {
        $this->Collection->add(new Round(1.0, 285.12));
        $this->Collection->add(new Round(1.0, 260.74));
        $this->Collection->roundDurations();

        $this->assertEquals(285, $this->Collection[0]->getDuration());
        $this->assertEquals(261, $this->Collection[1]->getDuration());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithBadTypes()
    {
        new RoundCollection([
            new Round(1.0, 255),
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

    public function testComparison()
    {
        $this->Collection->add(new Round(1.0, 285));
        $this->Collection->add(new Round(1.0, 260));

        $this->assertTrue($this->Collection->isEqualTo(clone $this->Collection));
        $this->assertFalse($this->Collection->isEqualTo(new RoundCollection()));
    }

    public function testClearingCollection()
    {
        $this->Collection->add(new Round(1.0, 285));
        $this->Collection->clear();

        $this->assertTrue($this->Collection->isEmpty());
        $this->assertEquals(0, $this->Collection->count());
    }
}
