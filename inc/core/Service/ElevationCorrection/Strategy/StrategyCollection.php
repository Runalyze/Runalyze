<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

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
     * @param array $latitudes
     * @param array $longitudes
     *
     * @return array|null altitude [m]
     *
     * @throws \InvalidArgumentException
     */
    public function loadAltitudeData(array $latitudes, array $longitudes)
    {
        if (empty($latitudes)) {
            return null;
        }

        if (count($latitudes) != count($longitudes)) {
            throw new \InvalidArgumentException('Latitudes and longitudes must be of same size.');
        }

        foreach ($this->Strategies as $index => $strategy) {
            if ($strategy->isPossible()) {
                $result = $strategy->loadAltitudeData($latitudes, $longitudes);

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
