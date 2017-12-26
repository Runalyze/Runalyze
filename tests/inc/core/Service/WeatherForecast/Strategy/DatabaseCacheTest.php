<?php

namespace Runalyze\Tests\Service\WeatherForecast\Strategy;

use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Service\WeatherForecast\DatabaseCacheInterface;
use Runalyze\Service\WeatherForecast\Location;
use Runalyze\Service\WeatherForecast\Strategy\DatabaseCache;

class DatabaseCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param WeatherData|null $data
     * @return \PHPUnit_Framework_MockObject_MockObject|DatabaseCacheInterface
     */
    protected function getMockForResponse(WeatherData $data = null)
    {
        $mock = $this->getMock(DatabaseCacheInterface::class);
        $mock->method('getCachedWeatherDataFor')
            ->willReturn($data);

        /** @var DatabaseCacheInterface $mock */
        return $mock;
    }

    public function testEmptyResponse()
    {
        $cache = new DatabaseCache($this->getMockForResponse(null));

        $this->assertNull($cache->loadForecast(new Location()));
    }

    public function testSimpleResponse()
    {
        $data = new WeatherData();
        $data->Temperature = 17;
        $data->WindDirection = 314;
        $data->WindSpeed = 42;

        $cache = new DatabaseCache($this->getMockForResponse($data));

        $this->assertEquals($data, $cache->loadForecast(new Location()));
    }
}
