<?php

namespace Runalyze\Calculation;

use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geohash\Geohash;
use Runalyze\Model\Activity;
use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;
use Runalyze\View\Activity\FakeContext;

class NightDetectorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyDetector()
    {
        $Detector = new NightDetector();

        $this->assertFalse($Detector->isKnown());
        $this->assertFalse($Detector->isNight());
        $this->assertNull($Detector->value());
    }

    /** @expectedException \InvalidArgumentException */
    public function testInvalidTimestamp()
    {
        $Detector = new NightDetector();
        $Detector->setFrom('foobar', new Coordinate([0.0, 0.0]));
    }

    public function testSomeValuesFromCentralEurope()
    {
        $this->checkData([
            ['2016-01-13 08:00:00', [49.44, 7.45], true], // Kaiserslautern, de
            ['2016-01-13 17:00:00', [49.44, 7.45], true],
            ['2016-01-21 17:00:00', [49.44, 7.45], false],
            ['2016-02-03 08:00:00', [49.44, 7.45], false],
            ['2016-03-26 07:00:00', [49.44, 7.45], false],
            ['2016-03-26 19:00:00', [49.44, 7.45], true],
            ['2016-03-27 07:00:00', [49.44, 7.45], true], // summertime
            ['2016-03-27 19:00:00', [49.44, 7.45], false],
            ['2016-10-29 08:00:00', [49.44, 7.45], true],
            ['2016-10-29 18:00:00', [49.44, 7.45], false],
            ['2016-10-30 08:00:00', [49.44, 7.45], false], // wintertime
            ['2016-10-30 18:00:00', [49.44, 7.45], true],
            ['2016-10-29 08:00:00 Europe/Berlin', [49.44, 7.45], true], // with explicit central europe time zone
            ['2016-10-29 18:00:00 Europe/Berlin', [49.44, 7.45], false],
            ['2016-10-30 08:00:00 Europe/Berlin', [49.44, 7.45], false], // wintertime
            ['2016-10-30 18:00:00 Europe/Berlin', [49.44, 7.45], true],
        ]);
    }

    /**
     * @param array $testData [['time string', [lat, lng], true|false], ...]
     */
    protected function checkData(array $testData)
    {
        foreach ($testData as $data) {
            $this->assertEquals(
                $data[2],
                (new NightDetector(strtotime($data[0]), new Coordinate($data[1])))->isNight(),
                'Tried to test '.$data[0].' at '.implode('/', $data[1])
            );
        }
    }

    public function testThatDurationIsRespected()
    {
        $Detector = new NightDetector();
        $Context = FakeContext::withDefaultSport(
            new Activity\Entity([
                Activity\Entity::TIMESTAMP => strtotime('2016-01-13 08:00:00'),
                Activity\Entity::TIME_IN_SECONDS => 0
            ]), new Trackdata\Entity([
            ]), new Route\Entity([
                Route\Entity::GEOHASHES => [
                    (new Geohash())->encode(new Coordinate([49.44, 7.45]))->getGeohash()
                ]
            ])
        );
        $Context->route()->synchronize();

        $this->assertTrue($Detector->setFromContext($Context)->isNight());

        $Context->activity()->set(Activity\Entity::TIME_IN_SECONDS, 3600);
        $this->assertFalse($Detector->setFromContext($Context)->isNight());
    }

    public function testContextWithoutRoute()
    {
        $Detector = new NightDetector();
        $Detector->setFromContext(FakeContext::onlyWithActivity(
            new Activity\Entity([
                Activity\Entity::TIMESTAMP => time(),
                Activity\Entity::TIME_IN_SECONDS => 600
            ]))
        );

        $this->assertFalse($Detector->isKnown());
    }

    public function testRouteWithoutCoordinates()
    {
        $Detector = new NightDetector();
        $Detector->setFromEntities(
            new Activity\Entity([
                Activity\Entity::TIMESTAMP => time(),
                Activity\Entity::TIME_IN_SECONDS => 600
            ]), new Route\Entity([
                Route\Entity::DISTANCE => 10
            ])
        );

        $this->assertFalse($Detector->isKnown());
    }
}