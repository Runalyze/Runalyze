<?php
/**
 * This file contains class::GoogleMaps
 * @package Runalyze\Service\ElevationCorrection\Strategy
 */

namespace Runalyze\Service\ElevationCorrection\Strategy;

/**
 * Elevation corrector strategy: http://maps.googleapis.com/
 *
 * @author Hannes Christiansen
 * @package Runalyze\Service\ElevationCorrection\Strategy
 */
class GoogleMaps extends AbstractStrategyFromExternalAPI
{
	/**
	 * Points per call
	 * @var int
	 */
	protected $POINTS_PER_CALL = 20;

	/**
	 * Value for unknown elevation
	 *
	 * GoogleMaps does not mask oceans but returns real elevation, e.g. -3492m for (0.0, 0.0).
	 * We will mask only this 'null point' as 'unknown'.
	 *
	 * @see http://maps.googleapis.com/maps/api/elevation/json?locations=0,0&sensor=false
	 * @var int
	 */
	protected $UnknownValue = -3492;

	/**
	 * Can the strategy handle the data?
	 *
	 * We assume that GoogleMaps will find elevation data for all points.
	 *
	 * @see https://developers.google.com/maps/documentation/elevation/?hl=de&csw=1
	 */
	public function canHandleData()
	{
		$url = 'http://maps.googleapis.com/maps/api/elevation/json?locations=49.4,7.7&sensor=false';
		$response = json_decode(\Filesystem::getExternUrlContent($url), true);

		if (null === $response) {
			return false;
		}

		if (is_array($response) && isset($response['results']) && !empty($response['results'])) {
			return true;
		}

		if (isset($response['status']) && 'OVER_QUERY_LIMIT' != $response['status']) {
		    throw new InvalidResponseException('GoogleMaps returned no data. (status: "'.$response['status'].'")');
		}

		return false;
	}

	/**
	 * Fetch elevation
	 * @param array $latitudes
	 * @param array $longitudes
	 * @return array
	 * @throws \Runalyze\Service\ElevationCorrection\Strategy\InvalidResponseException
	 */
	protected function fetchElevationFor(array $latitudes, array $longitudes)
	{
		$numberOfCoordinates = count($latitudes);
		$coordinatesString = '';

		for ($i = 0; $i < $numberOfCoordinates; $i++) {
			$coordinatesString .= $latitudes[$i].','.$longitudes[$i].'|';
		}

		$url = 'http://maps.googleapis.com/maps/api/elevation/json?locations='.substr($coordinatesString, 0, -1).'&sensor=false';
		$response = json_decode(\Filesystem::getExternUrlContent($url), true);

		if (null === $response) {
		    throw new NoResponseException('Request for '.$url.' failed without response.');
        }

        if (is_array($response) && isset($response['status']) && 'OVER_QUERY_LIMIT' == $response['status']) {
		    throw new OverQueryLimitException($response['error_message']);
        }

		if (!is_array($response) || !isset($response['results']) || !isset($response['results'][0]['elevation'])) {
			throw new InvalidResponseException('GoogleMaps returned malformed code.');
		}

		$elevationData = array();
		$responseLength = count($response['results']);

		for ($i = 0; $i < $responseLength; $i++) {
			$elevationData[] = (int)$response['results'][$i]['elevation'];
		}

		return $elevationData;
	}
}
