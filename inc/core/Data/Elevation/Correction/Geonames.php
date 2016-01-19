<?php
/**
 * This file contains class::Geonames
 * @package Runalyze\Data\Elevation\Correction
 */

namespace Runalyze\Data\Elevation\Correction;

/**
 * Elevation corrector strategy: ws.geonames.org
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Elevation\Correction
 */
class Geonames extends FromExternalAPI {
	/**
	 * Username
	 * @var string
	 * @TODO Use this from admin configuration
	 */
	protected $USERNAME = 'runalyze';

	/**
	 * Can the strategy handle the data?
	 * 
	 * To test this, we try to fetch a gtopo30-value.
	 * This costs only 0.1 credit per call.
	 * 
	 * We assume that Geonames will find elevation data for all points.
	 * 
	 * @see http://www.geonames.org/export/webservice-exception.html
	 */
	public function canHandleData() {
		$url = 'http://api.geonames.org/gtopo30JSON?lat=47.01&lng=10.2&username='.$this->USERNAME;
		$response = json_decode(\Filesystem::getExternUrlContent($url), true);

		if (is_null($response))
			return false;

		if (isset($response['gtopo30']))
			return true;

		if (isset($response['status']) && isset($response['status']['value'])) {
			switch ((int)$response['status']['value']) {
				case 10:
					\Runalyze\Error::getInstance()->addWarning('Geonames user account is not valid.');
					break;
				case 18:
					\Runalyze\Error::getInstance()->addDebug('Geonames-request failed: daily limit of credits exceeded');
					break;
				case 19:
					\Runalyze\Error::getInstance()->addDebug('Geonames-request failed: hourly limit of credits exceeded');
					break;
				case 20:
					\Runalyze\Error::getInstance()->addDebug('Geonames-request failed: weekly limit of credits exceeded');
					break;
				default:
					if (isset($response['status']['message']))
						\Runalyze\Error::getInstance ()->addDebug('Geonames response: '.$response['status']['message']);
			}
		}

		return false;
	}

	/**
	 * Fetch elevation
	 * @param array $latitudes
	 * @param array $longitudes
	 * @return array
	 * @throws \RuntimeException
	 */
	protected function fetchElevationFor(array $latitudes, array $longitudes) {
		$latitudeString = implode(',', $latitudes);
		$longitudeString = implode(',', $longitudes);

		$url = 'http://api.geonames.org/srtm3JSON?lats='.$latitudeString.'&lngs='.$longitudeString.'&username='.$this->USERNAME;
		$response = json_decode(\Filesystem::getExternUrlContent($url), true);

		if (is_null($response) || !isset($response['geonames']) || !is_array($response['geonames'])) {
			throw new \RuntimeException('Geonames returned malformed code.');
		}

		$elevationData = array();
		$responseLength = count($response['geonames']);

		for ($i = 0; $i < $responseLength; $i++) {
			$elevationData[] = (int)$response['geonames'][$i]['srtm3'];
		}

		return $elevationData;
	}
}