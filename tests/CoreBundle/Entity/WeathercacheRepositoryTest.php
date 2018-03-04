<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Weathercache;
use Runalyze\Bundle\CoreBundle\Entity\WeathercacheRepository;
use Runalyze\Profile\Weather\Source\WeatherSourceProfile;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\Service\WeatherForecast\Location;

/**
 * @group requiresDoctrine
 */
class WeathercacheRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var WeathercacheRepository */
    protected $Repository;

    /** @var Location */
    protected $Location;

    protected function setUp()
    {
        parent::setUp();

        $this->Repository = $this->EntityManager->getRepository('CoreBundle:Weathercache');

        $this->Location = new Location();
        $this->Location->setGeohash('u1xjn3n74zxv');
        $this->Location->setDateTime((new \DateTime())->setTimestamp(1234567890));
    }

    protected function insertExampleWeatherData()
    {
        $cache = new Weathercache();
        $cache->setGeohash('u1xjn3n74zxv');
        $cache->setTime(1234567890);
        $cache->setTemperature(10);
        $cache->setWindSpeed(25);
        $cache->setWindDeg(302);
        $cache->setHumidity(86);
        $cache->setPressure(1033);
        $cache->setWeatherid(WeatherConditionProfile::CLOUDY);
        $cache->setWeatherSource(WeatherSourceProfile::OPEN_WEATHER_MAP);

        $this->Repository->save($cache);
    }

    public function testEmptyCache()
    {
        $this->assertNull($this->Repository->getCachedWeatherDataFor($this->Location, 1800));
    }

    public function testFetchingCachedWeatherData()
    {
        $this->insertExampleWeatherData();

        $result = $this->Repository->getCachedWeatherDataFor($this->Location, 1800);

        $this->assertNotNull($result);
        $this->assertEquals(10, $result->Temperature);
        $this->assertEquals(25, $result->WindSpeed);
        $this->assertEquals(302, $result->WindDirection);
        $this->assertEquals(86, $result->Humidity);
        $this->assertEquals(1033, $result->AirPressure);
        $this->assertEquals(WeatherConditionProfile::CLOUDY, $result->InternalConditionId);
        $this->assertEquals(WeatherSourceProfile::OPEN_WEATHER_MAP, $result->Source);
    }

    public function testWrongGeohashOrTime()
    {
        $this->insertExampleWeatherData();

        $this->Location->setGeohash('u1xm2f8jc');

        $this->assertNull($this->Repository->getCachedWeatherDataFor($this->Location, 1800));

        $this->Location->setGeohash('u1xjn3n74zxv');
        $this->Location->setDateTime((new \DateTime())->setTimestamp(9876543210));

        $this->assertNull($this->Repository->getCachedWeatherDataFor($this->Location, 1800));
    }

    public function testTimeTolerance()
    {
        $this->insertExampleWeatherData();

        $this->Location->setDateTime((new \DateTime())->setTimestamp(1234567890 - 1800 + 1));

        $this->assertNotNull($this->Repository->getCachedWeatherDataFor($this->Location, 1800));

        $this->Location->setDateTime((new \DateTime())->setTimestamp(1234567890 + 1800 - 1));

        $this->assertNotNull($this->Repository->getCachedWeatherDataFor($this->Location, 1800));
        $this->assertNull($this->Repository->getCachedWeatherDataFor($this->Location, 1700));
    }
}
