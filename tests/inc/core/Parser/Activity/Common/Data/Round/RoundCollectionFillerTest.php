<?php

namespace Runalyze\Tests\Parser\Activity\Common\Data\Round;

use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollectionFiller;

class RoundCollectionFillerTest extends \PHPUnit_Framework_TestCase
{
    public function testEasyCompletionByDistanceArray()
    {
        $collection = new RoundCollection([
            new Round(0.0, 100),
            new Round(0.0, 120)
        ]);

        $filler = new RoundCollectionFiller($collection);
        $filler->fillDistancesFromArray(
            [0, 50, 100, 150, 200, 220],
            [0.0, 0.2, 0.4, 0.6, 0.8, 0.9]
        );

        $this->assertEquals(0.9, $collection->getTotalDistance());
        $this->assertEquals(0.4, $collection[0]->getDistance());
        $this->assertEquals(0.5, $collection[1]->getDistance());
    }

    public function testEasyCompletionByTimeArray()
    {
        $collection = new RoundCollection([
            new Round(0.4, 0),
            new Round(0.5, 0)
        ]);

        $filler = new RoundCollectionFiller($collection);
        $filler->fillTimesFromArray(
            [0, 50, 100, 150, 200, 220],
            [0.0, 0.2, 0.4, 0.6, 0.8, 0.9]
        );

        $this->assertEquals(220, $collection->getTotalDuration());
        $this->assertEquals(100, $collection[0]->getDuration());
        $this->assertEquals(120, $collection[1]->getDuration());
    }

    public function testThatCompletionWithEmptyCollectionDoesNotFail()
    {
        $collection = new RoundCollection();

        $filler = new RoundCollectionFiller($collection);
        $filler->fillDistancesFromArray([1, 2, 3], [0.01, 0.02, 0.03]);
        $filler->fillTimesFromArray([1, 2, 3], [0.01, 0.02, 0.03]);
    }

    public function testWithTooShortDistanceArray()
    {
        $collection = new RoundCollection([
            new Round(0.4, 0),
            new Round(0.4, 0)
        ]);

        $filler = new RoundCollectionFiller($collection);
        $filler->fillTimesFromArray(
            [30, 60, 90],
            [0.2, 0.4, 0.6]
        );

        $this->assertEquals(60, $collection[0]->getDuration());
    }
}
