<?php

namespace Runalyze\Service\WeatherForecast;

use Runalyze\Parser\Activity\Common\Data\WeatherData;

interface DatabaseCacheInterface
{
    /**
     * @param Location $location
     * @param int $timeTolerance [s]
     *
     * @return WeatherData|null
     */
    public function getCachedWeatherDataFor(Location $location, $timeTolerance);

    /**
     * @param WeatherData $data
     * @param Location $location
     */
    public function cacheWeatherData(WeatherData $data, Location $location);
}
