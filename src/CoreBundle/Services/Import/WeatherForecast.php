<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Service\WeatherForecast\DatabaseCacheInterface;
use Runalyze\Service\WeatherForecast\Location;
use Runalyze\Service\WeatherForecast\Strategy\DarkSky;
use Runalyze\Service\WeatherForecast\Strategy\DatabaseCache;
use Runalyze\Service\WeatherForecast\Strategy\OpenWeatherMap;
use Runalyze\Service\WeatherForecast\Strategy\StrategyCollection;
use Runalyze\Service\WeatherForecast\Strategy\StrategyInterface;

class WeatherForecast
{
    /** @var StrategyCollection */
    protected $StrategyCollection;

    /** @var DatabaseCacheInterface */
    protected $DatabaseCache;

    public function __construct(
        DatabaseCache $cache,
        DarkSky $darkSky,
        OpenWeatherMap $openWeatherMap,
        DatabaseCacheInterface $databaseCache
    )
    {
        $this->StrategyCollection = new StrategyCollection();
        $this->StrategyCollection->add($cache);
        $this->StrategyCollection->add($darkSky);
        $this->StrategyCollection->add($openWeatherMap);

        $this->DatabaseCache = $databaseCache;
    }

    /**
     * @param Location $location
     * @return null|\Runalyze\Parser\Activity\Common\Data\WeatherData
     */
    public function loadForecast(Location $location)
    {
        $result = $this->StrategyCollection->tryToLoadForecast($location);

        if (null !== $result) {
            $this->cacheWeatherData($result, $location, $this->StrategyCollection->getLastSuccessfulStrategy());
        }

        return $result;
    }

    protected function cacheWeatherData(WeatherData $data, Location $location, StrategyInterface $strategy)
    {
        if ($strategy->isCachable()) {
            $this->DatabaseCache->cacheWeatherData($data, $location);
        }
    }
}
