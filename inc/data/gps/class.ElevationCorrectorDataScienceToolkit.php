<?php
/**
 * This file contains class::ElevationCorrectorDataScienceToolkit
 * @package Runalyze\Data\GPS\Elevation
 */
/**
 * Elevation corrector strategy: datasciencetoolkit.org
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS\Elevation
 */
class ElevationCorrectorDataScienceToolkit extends ElevationCorrectorFromExternalAPI {
	/**
	 * Points per call
	 * @var int
	 */
	protected $POINTS_PER_CALL = 50;

	/**
	 * Can the strategy handle the data?
	 * 
	 * We assume that DataScienceToolkit will find elevation data for all points.
	 * 
	 * @see http://www.datasciencetoolkit.org/developerdocs#coordinates2statistics
	 */
	public function canHandleData() {
		$url = 'http://www.datasciencetoolkit.org/coordinates2statistics/49.4%2c7.7?statistics=elevation';
		$response = json_decode(Filesystem::getExternUrlContent($url), true);

		if (is_null($response))
			return false;

		if (is_array($response) && isset($response[0]['statistics']))
			return true;

		if (isset($response['error']))
			Error::getInstance ()->addDebug('DataScienceToolkit response: '.$response['error']);

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
		$coordinatesArray = array();

		for ($i = 0; $i < $numberOfCoordinates; $i++) {
			$coordinatesArray[] = array($latitudes[$i], $longitudes[$i]);
		}

		$coordinatesString = json_encode($coordinatesArray);

		$url = 'http://www.datasciencetoolkit.org/coordinates2statistics/'.$coordinatesString.'?statistics=elevation';
		$response = json_decode(Filesystem::getExternUrlContent($url), true);

		if (is_null($response) || !is_array($response) || !isset($response[0]['statistics']) || !isset($response[0]['statistics']['elevation']))
			throw new RuntimeException('DataScienceToolkit returned malformed code.');

		$elevationData = array();
		$responseLength = count($response);

		for ($i = 0; $i < $responseLength; $i++)
			$elevationData[] = (int)$response[$i]['statistics']['elevation']['value'];

		return $elevationData;
	}
}