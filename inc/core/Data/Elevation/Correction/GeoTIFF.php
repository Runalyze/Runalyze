<?php
/**
 * This file contains class::GeoTIFF
 * @package Runalyze\Data\Elevation\Correction
 */

namespace Runalyze\Data\Elevation\Correction;

/**
 * Elevation corrector strategy: GeoTIFF
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Elevation\Correction
 */
class GeoTIFF extends Strategy {
	/**
	 * @var int
	 */
	const UNKNOWN = -32768;

	/**
	 * Reader
	 * @var \SRTMGeoTIFFReader
	 */
	protected $Reader = null;

	/**
	 * Boolean flag: use smoothing
	 * @var boolean
	 */
	protected $USE_SMOOTHING = true;

	/**
	 * Boolean flag: guess unknown
	 * @var boolean
	 */
	protected $GUESS_UNKNOWN = true;

	/**
	 * Boolean flag: interpolate
	 * @var boolean
	 */
	protected $INTERPOLATE = true;

	/**
	 * Set use smoothing flag
	 * @param boolean $flag
	 */
	public function setUseSmoothing($flag) {
		$this->USE_SMOOTHING = $flag;
	}

	/**
	 * Set guess unknown flag
	 * @param boolean $flag
	 */
	public function setGuessUnknown($flag) {
		$this->GUESS_UNKNOWN = $flag;
	}

	/**
	 * Can the strategy handle the data?
	 */
	public function canHandleData() {
		$minLatitude = min($this->LatitudePoints);
		$maxLatitude = max($this->LatitudePoints);
		$minLongitude = min($this->LongitudePoints);
		$maxLongitude = max($this->LongitudePoints);

		$testArray = array(
			$minLatitude, $minLongitude,
			$minLatitude, $maxLongitude,
			$maxLatitude, $minLongitude,
			$maxLatitude, $maxLongitude
		);

		try {
			$this->Reader = new \SRTMGeoTIFFReader(FRONTEND_PATH.'../data/srtm');
			$this->Reader->getMultipleElevations($testArray);

			return true;
		} catch (\Exception $Exception) {
			//\Error::getInstance()->addDebug($Exception->getMessage());
			return false;
		}
	}

	/**
	 * Correct elevation
	 * 
	 * Note: canHandleData() has to be called before!
	 */
	public function correctElevation() {
		if ($this->Reader instanceof \SRTMGeoTIFFReader) {
			$this->Reader->maxPoints = PHP_INT_MAX;
			$arraySize = count($this->LatitudePoints);
			$locations = array();

			for ($i = 0; $i < $arraySize; $i++) {
				$locations[] = $this->LatitudePoints[$i];
				$locations[] = $this->LongitudePoints[$i];
			}

			$this->ElevationPoints = $this->Reader->getMultipleElevations($locations, false, $this->INTERPOLATE);

			if ($this->USE_SMOOTHING) {
				$this->smoothElevation();
			}

			if ($this->GUESS_UNKNOWN) {
				$this->guessUnknown(self::UNKNOWN);
			}
		}
	}

	/**
	 * Smooth elevation
	 * 
	 * Although this could be more exactly, a smoothing has to be used.
	 * Otherwise, this corrector would result in much higher cumulative elevations.
	 */
	protected function smoothElevation() {
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