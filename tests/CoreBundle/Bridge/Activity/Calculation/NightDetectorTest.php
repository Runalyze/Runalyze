<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Bridge\Activity\Calculation;

use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geohash\Geohash;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\NightDetector;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Util\LocalTime;

class NightDetectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var NightDetector */
    protected $Detector;

    /** @var Training */
    protected $Activity;

    protected function setUp()
    {
        $this->Activity = new Training();
        $this->Activity->setRoute(new Route());
        $this->Detector = new NightDetector();
    }

    public function testEmptyActivity()
    {
        $this->assertNull($this->Detector->isActivityAtNight($this->Activity));
    }

    public function testActivityWithoutRoute()
    {
        $this->Activity->setRoute(null);

        $this->assertNull($this->Detector->isActivityAtNight($this->Activity));
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
        $this->Activity->getRoute()->setGeohashes([]);

        foreach ($testData as $data) {
            $this->Activity->setTime(LocalTime::fromServerTime(strtotime($data[0]))->getTimestamp());
            $this->Activity->getRoute()->setStartpoint((new Geohash())->encode(new Coordinate($data[1]))->getGeohash());

            $this->assertEquals(
                $data[2],
                $this->Detector->isActivityAtNight($this->Activity),
                'Tried to test '.$data[0].' at '.implode('/', $data[1])
            );
        }
    }

    public function testThatDurationIsRespected()
    {
        $this->Activity->getRoute()->setGeohashes([]);
        $this->Activity->setTime(LocalTime::fromServerTime(strtotime('2016-01-13 08:00:00'))->getTimestamp());
        $this->Activity->getRoute()->setStartpoint((new Geohash())->encode(new Coordinate([49.44, 7.45]))->getGeohash());

        $this->assertTrue($this->Detector->isActivityAtNight($this->Activity));

        $this->Activity->setS(3600);

        $this->assertFalse($this->Detector->isActivityAtNight($this->Activity));
    }
}
