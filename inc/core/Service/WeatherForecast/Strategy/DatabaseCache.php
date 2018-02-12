<?php

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Service\WeatherForecast\Location;
use Runalyze\Service\WeatherForecast\DatabaseCacheInterface;

class DatabaseCache implements StrategyInterface
{
    /** @var int [s] time range for cache lookup (in seconds) (+/- 30 min) */
    const TIME_PRECISION = 1800;

    /** @var DatabaseCacheInterface */
    protected $Cache;

    public function __construct(DatabaseCacheInterface $cache)
    {
        $this->Cache = $cache;
    }

    public function isPossible()
    {
        return true;
    }

    public function isCachable()
    {
        return false;
    }

    public function loadForecast(Location $location)
    {
        return $this->Cache->getCachedWeatherDataFor($location, self::TIME_PRECISION);
    }
}
