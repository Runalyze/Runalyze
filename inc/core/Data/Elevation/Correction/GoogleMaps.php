<?php
/**
 * This file contains class::GoogleMaps
 * @package Runalyze\Data\Elevation\Correction
 */

namespace Runalyze\Data\Elevation\Correction;

/**
 * Elevation corrector strategy: http://maps.googleapis.com/
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Elevation\Correction
 */
class GoogleMaps extends FromExternalAPI {
	/**
	 * Points per call
	 * @var int
	 */
	protected $POINTS_PER_CALL = 20;

	/**
	 * Can the strategy handle the data?
	 * 
	 * We assume that GoogleMaps will find elevation data for all points.
	 * 
	 * @see https://developers.google.com/maps/documentation/elevation/?hl=de&csw=1
	 */
	public function canHandleData() {
		$url = 'http://maps.googleapis.com/maps/api/elevation/json?locations=49.4,7.7&sensor=false';
		$response = json_decode(\Filesystem::getExternUrlContent($url), true);

		if (is_null($response)) {
			return false;
		}

		if (is_array($response) && isset($response['results'])) {
			return true;
		}

		if (isset($response['status'])) {
			\Runalyze\Error::getInstance ()->addDebug('GoogleMaps response: '.$response['status']);
		}

		return false;
	}

	/**
	 * Fetch elevation
	 * @param array $latitudes
	 * @param array $longitudes
	 * @return array
	 */
	protected function fetchElevationFor(array $latitudes, array $longitudes) {
		$numberOfCoordinates = count($latitudes);
		$coordinatesString = '';

		for ($i = 0; $i < $numberOfCoordinates; $i++) {
			$coordinatesString .= $latitudes[$i].','.$longitudes[$i].'|';
		}

		$url = 'http://maps.googleapis.com/maps/api/elevation/json?locations='.substr($coordinatesString, 0, -1).'&sensor=false';
		$response = json_decode(\Filesystem::getExternUrlContent($url), true);

		if (is_null($response) || !is_array($response) || !isset($response['results']) || !isset($response['results'][0]['elevation'])) {
			throw new \RuntimeException('GoogleMaps returned malformed code.');
		}

		$elevationData = array();
		$responseLength = count($response['results']);

		for ($i = 0; $i < $responseLength; $i++) {
			$elevationData[] = (int)$response['results'][$i]['elevation'];
		}

		return $elevationData;
	}
}