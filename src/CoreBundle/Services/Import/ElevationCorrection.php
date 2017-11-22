<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Runalyze\Service\ElevationCorrection\Strategy\Geonames;
use Runalyze\Service\ElevationCorrection\Strategy\GeoTiff;
use Runalyze\Service\ElevationCorrection\Strategy\GoogleMaps;
use Runalyze\Service\ElevationCorrection\Strategy\StrategyCollection;
use Runalyze\Service\ElevationCorrection\Strategy\StrategyInterface;

class ElevationCorrection
{
    /** @var StrategyCollection */
	protected $StrategyCollection;

	public function __construct(
	    GeoTiff $geoTiff,
        Geonames $geonames,
        GoogleMaps $googleMaps
    )
    {
        $this->StrategyCollection = new StrategyCollection();
        $this->StrategyCollection->add($geoTiff);
        $this->StrategyCollection->add($geonames);
        $this->StrategyCollection->add($googleMaps);
    }

    /**
     * @param float[] $latitudes
     * @param float[] $longitudes
     * @param null|StrategyInterface $strategy
     *
     * @return int[]|null altitude [m]
     */
    public function loadAltitudeData(array $latitudes, array $longitudes, StrategyInterface $strategy = null)
    {
        if (null !== $strategy) {
            return $strategy->loadAltitudeData($latitudes, $longitudes);
        }

        return $this->StrategyCollection->loadAltitudeData($latitudes, $longitudes);
    }

    /**
     * @return null|StrategyInterface
     */
    public function getLastSuccessfulStrategy()
    {
        return $this->StrategyCollection->getLastSuccessfulStrategy();
    }
}
