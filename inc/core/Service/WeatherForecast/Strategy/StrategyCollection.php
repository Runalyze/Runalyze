<?php

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Service\WeatherForecast\Location;

class StrategyCollection
{
    /** @var StrategyInterface[] */
    protected $Strategies = [];

    /** @var bool|int */
    protected $LastSuccessfulStrategyIndex = false;

    public function add(StrategyInterface $strategy)
    {
        $this->Strategies[] = $strategy;
    }

    /**
     * @param Location $location
     *
     * @return null|\Runalyze\Parser\Activity\Common\Data\WeatherData
     */
    public function tryToLoadForecast(Location $location)
    {
        foreach ($this->Strategies as $index => $strategy) {
            if ($strategy->isPossible()) {
                $result = $strategy->loadForecast($location);

                if (null !== $result) {
                    $this->LastSuccessfulStrategyIndex = $index;

                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * @return null|StrategyInterface
     */
    public function getLastSuccessfulStrategy()
    {
        if (false !== $this->LastSuccessfulStrategyIndex) {
            return $this->Strategies[$this->LastSuccessfulStrategyIndex];
        }

        return null;
    }
}
