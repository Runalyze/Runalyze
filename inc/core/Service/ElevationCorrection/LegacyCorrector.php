<?php

namespace Runalyze\Service\ElevationCorrection;

use GuzzleHttp\Client;
use Runalyze\Bundle\CoreBundle\Services\Import\GeoTiffReader;
use Runalyze\Service\ElevationCorrection\Exception\NoValidStrategyException;
use Runalyze\Service\ElevationCorrection\Exception\StrategyException;
use Runalyze\Service\ElevationCorrection\Strategy\Geonames;
use Runalyze\Service\ElevationCorrection\Strategy\GeoTiff;
use Runalyze\Service\ElevationCorrection\Strategy\GoogleMaps;

class LegacyCorrector
{
	/** @var null|\Runalyze\Service\ElevationCorrection\Strategy\StrategyInterface */
	protected $Strategy = null;

	/** @var float[] */
	protected $LatitudePoints;

	/** @var float[] */
	protected $LongitudePoints;

	/** @var int[]|null [m] */
	protected $Altitudes;

	/** @var GeoTiff */
	protected $GeoTiff;

	/** @var Geonames */
	protected $Geonames;

	/** @var GoogleMaps */
	protected $GoogleMaps;

	public function __construct()
    {
        $httpClient = new Client();

        $this->GeoTiff = new GeoTiff(new GeoTiffReader(DATA_DIRECTORY.'/srtm'));
        $this->Geonames = new Geonames(GEONAMES_USERNAME, $httpClient);
        $this->GoogleMaps = new GoogleMaps($httpClient);
    }

    /**
	 * @param array $latitude
	 * @param array $longitude
	 * @param string $strategyName
     *
	 * @throws \Runalyze\Service\ElevationCorrection\Exception\NoValidStrategyException
	 * @throws \Runalyze\Service\ElevationCorrection\Exception\InvalidResponseException
	 */
	public function correctElevation(array $latitude, array $longitude, $strategyName = '')
	{
		$this->LatitudePoints = $latitude;
		$this->LongitudePoints = $longitude;
		$this->Altitudes = null;
		$this->Strategy = null;

		if ($strategyName != '') {
			$this->tryToUse($strategyName);
		} else {
			$this->tryStrategies();
		}

		if (null === $this->Altitudes) {
            throw new NoValidStrategyException('No elevation correction strategy is able to handle the data. Maybe all query limits are reached.');
        }
	}

	/**
	 * @param string $strategyName
	 */
	protected function tryToUse($strategyName)
	{
	    switch ($strategyName) {
            case 'GeoTIFF':
                $this->Strategy = $this->GeoTiff;
                break;

            case 'Geonames':
                $this->Strategy = $this->Geonames;
                break;

            case 'GoogleMaps':
                $this->Strategy = $this->GoogleMaps;
                break;
        }

        if (null !== $this->Strategy && $this->Strategy->isPossible()) {
	        try {
                $this->Altitudes = $this->Strategy->loadAltitudeData($this->LatitudePoints, $this->LongitudePoints);
            } catch (StrategyException $e) {
	            // Main method will throw NoValidStrategyException
            }
        }
	}

	protected function tryStrategies()
	{
	    $strategies = ['GeoTIFF', 'Geonames', 'GoogleMaps'];

	    foreach ($strategies as $strategyName) {
	        if (null === $this->Altitudes) {
	            $this->tryToUse($strategyName);
            }
        }
	}

	/**
	 * @return string
	 */
	public function getNameOfUsedStrategy()
	{
		$strategyName = get_class($this->Strategy);

		return substr($strategyName, strrpos($strategyName, '\\')+1);
	}

	/**
	 * @return array
	 */
	public function getCorrectedElevation()
	{
		return $this->Altitudes ?: [];
	}
}
