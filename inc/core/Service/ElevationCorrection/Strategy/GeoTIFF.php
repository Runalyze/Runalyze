<?php
/**
 * This file contains class::GeoTIFF
 * @package Runalyze\Service\ElevationCorrection\Strategy
 */

namespace Runalyze\Service\ElevationCorrection\Strategy;

use Runalyze\DEM;

/**
 * Elevation corrector strategy: GeoTIFF
 *
 * @author Hannes Christiansen
 * @package Runalyze\Service\ElevationCorrection\Strategy
 */
class GeoTIFF extends AbstractStrategy
{
    /** @var \Runalyze\DEM\Reader */
    protected $Reader;

    /** @var bool */
    protected $USE_SMOOTHING = true;

    /** @var bool */
    protected $GUESS_UNKNOWN = true;

    /**
     * GeoTIFF constructor
     * @param array $LatitudePoints
     * @param array $LongitudePoints
     */
	public function __construct(array $LatitudePoints, array $LongitudePoints)
	{
		parent::__construct($LatitudePoints, $LongitudePoints);

        $this->Reader = new DEM\Reader();
        $this->Reader->addProvider(
            new DEM\Provider\GeoTIFF\SRTM4Provider(
                DATA_DIRECTORY.'/srtm',
                new DEM\Interpolation\BilinearInterpolation()
            )
        );
	}

	/**
	 * Can the strategy handle the data?
	 * @return bool
	 */
	public function canHandleData()
	{
        return $this->Reader->hasDataFor($this->getBoundsFor($this->LatitudePoints, $this->LongitudePoints));
	}

    /**
     * @param bool $flag
     */
    public function setUseSmoothing($flag)
    {
        $this->USE_SMOOTHING = $flag;
    }

    /**
     * @param bool $flag
     */
    public function setGuessUnknown($flag)
    {
        $this->GUESS_UNKNOWN = $flag;
    }

    /**
     * Correct elevation
     */
    public function correctElevation()
    {
        $this->ElevationPoints = $this->Reader->getElevations($this->LatitudePoints, $this->LongitudePoints);

        if ($this->GUESS_UNKNOWN) {
            $this->guessUnknown(false);
        }

        if ($this->USE_SMOOTHING) {
            $this->smoothElevation();
        }
    }

    /**
     * @param  float[] $latitudes
     * @param  float[] $longitudes
     * @return array   array(array($lat, $lng), ...)
     */
    protected function getBoundsFor(array $latitudes, array $longitudes)
    {
        $filteredLatitudes = array_filter($latitudes);
        $filteredLongitudes = array_filter($longitudes);

        if (empty($filteredLatitudes) || empty($filteredLongitudes)) {
            return [];
        }

        $minLatitude = min($filteredLatitudes);
        $maxLatitude = max($filteredLatitudes);
        $minLongitude = min($filteredLongitudes);
        $maxLongitude = max($filteredLongitudes);

        return [
            [$minLatitude, $minLongitude],
            [$minLatitude, $maxLongitude],
            [$maxLatitude, $minLongitude],
            [$maxLatitude, $maxLongitude],
        ];
    }

	/**
	 * Smooth elevation
	 *
	 * Although this could be more exactly, a smoothing has to be used.
	 * Otherwise, this corrector would result in much higher cumulative elevations.
	 */
	protected function smoothElevation()
	{
		if (empty($this->ElevationPoints)) {
			return;
		}

		$arraySize = count($this->ElevationPoints);
		$currentValue = $this->ElevationPoints[0];

		for ($i = 0; $i < $arraySize; $i++) {
			if ($i % $this->POINTS_TO_GROUP == 0) {
				$currentValue = $this->ElevationPoints[$i];
			} else {
				$this->ElevationPoints[$i] = $currentValue;
			}
		}
	}
}
