<?php

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Service\WeatherForecast\Location;

interface StrategyInterface
{
    /**
     * @return bool
     */
    public function isPossible();

    /**
     * @return bool
     */
    public function isCachable();

    /**
     * @param Location $location
     *
     * @return WeatherData|null
     */
    public function loadForecast(Location $location);
}
