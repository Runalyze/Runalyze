<?php
/**
 * This file contains class::Corrector
 * @package Runalyze\Data\Elevation\Correction
 */

namespace Runalyze\Data\Elevation\Correction;

/**
 * Elevation corrector
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Elevation\Correction
 */
class Corrector {
	/**
	 * Strategy
	 * @var \Runalyze\Data\Elevation\Correction\Strategy
	 */
	protected $Strategy = null;

	/**
	 * Latitude points
	 * @var array
	 */
	protected $LatitudePoints;

	/**
	 * Longitude points
	 * @var array
	 */
	protected $LongitudePoints;

	/**
	 * Correct elevation
	 * @param array $latitude
	 * @param array $longitude
	 * @param string $strategyName
	 */
	public function correctElevation(array $latitude, array $longitude, $strategyName = '') {
		$this->LatitudePoints = $latitude;
		$this->LongitudePoints = $longitude;

		if ($strategyName != '') {
			$this->tryToUse($strategyName);
		} else {
			$this->chooseStrategy();
		}

		$this->applyStrategy();
	}

	/**
	 * Is a valid strategy set?
	 * @return boolean
	 */
	final protected function hasNoValidStrategy() {
		return !($this->Strategy instanceof Strategy);
	}

	/**
	 * @param string $strategyName
	 */
	protected function tryToUse($strategyName) {
		$strategyName = 'Runalyze\\Data\\Elevation\\Correction\\'.$strategyName;

		if (class_exists($strategyName)) {
			$this->Strategy = new $strategyName($this->LatitudePoints, $this->LongitudePoints);

			if (!$this->Strategy->canHandleData()) {
				$this->Strategy = null;
			}
		}
	}

	/**
	 * Choose strategy
	 */
	protected function chooseStrategy() {
		$this->tryToUseGeoTIFF();

		if ($this->hasNoValidStrategy()) {
			$this->tryToUseGeonames();
		}

		if ($this->hasNoValidStrategy()) {
			$this->tryToUseGoogleAPI();
		}
	}

	/**
	 * Apply strategy
	 * @throws \RuntimeException
	 */
	protected function applyStrategy() {
		if ($this->hasNoValidStrategy()) {
			throw new NoValidStrategyException('No elevation correction strategy is able to handle the data. Maybe all query limits are reached.');
		} else {
			$this->Strategy->correctElevation();
		}
	}

	/**
	 * Get used strategy
	 * @return string
	 */
	public function getNameOfUsedStrategy() {
		$strategyName = get_class($this->Strategy);

		return substr($strategyName, strrpos($strategyName, '\\')+1);
	}

	/**
	 * Get corrected elevation
	 * @return array
	 */
	public function getCorrectedElevation() {
		if ($this->hasNoValidStrategy()) {
			return array();
		} else {
			return $this->Strategy->getCorrectedElevation();
		}
	}

	/**
	 * Try to use GeoTIFF
	 */
	protected function tryToUseGeoTIFF() {
		$this->Strategy = new GeoTIFF($this->LatitudePoints, $this->LongitudePoints);

		if (!$this->Strategy->canHandleData()) {
			$this->Strategy = null;
		}
	}

	/**
	 * Try to use Geonames
	 */
	protected function tryToUseGeonames() {
		$this->Strategy = new Geonames($this->LatitudePoints, $this->LongitudePoints);

		if (!$this->Strategy->canHandleData()) {
			$this->Strategy = null;
		}
	}

	/**
	 * Try to use Google API
	 * 
	 * This method is currently not used.
	 * Googles terms do not allow to use the api without displaying the data on a map.
	 * As long as the other apis work, we do not need to use Google's api anymore.
	 * 
	 * @see https://developers.google.com/maps/terms?hl=de#section_10_12
	 */
	protected function tryToUseGoogleAPI() {
		$this->Strategy = new GoogleMaps($this->LatitudePoints, $this->LongitudePoints);

		if (!$this->Strategy->canHandleData()) {
			$this->Strategy = null;
		}
	}
}