<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model\Route
 */

namespace Runalyze\Model\Route;

use Runalyze\Model;
use League\Geotools\Geotools;
use League\Geotools\Geohash\Geohash;
use \League\Geotools\Coordinate\Coordinate;

/**
 * Route object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Route
 */
class Object extends Model\ObjectWithID implements Model\Loopable {
	/**
	 * Cities separator
	 * @var string
	 */
	const CITIES_SEPARATOR = ' - ';

	/**
	 * Key: name
	 * @var string
	 */
	const NAME = 'name';

	/**
	 * Key: cities
	 * @var string
	 */
	const CITIES = 'cities';

	/**
	 * Key: distance
	 * @var string
	 */
	const DISTANCE = 'distance';

	/**
	 * Key: elevation
	 * @var string
	 */
	const ELEVATION = 'elevation';

	/**
	 * Key: elevation up
	 * @var string
	 */
	const ELEVATION_UP = 'elevation_up';

	/**
	 * Key: elevation down
	 * @var string
	 */
	const ELEVATION_DOWN = 'elevation_down';

	/**
	 * Key: latitudes
	 * @var string
	 */
	const LATITUDES = 'lats';

	/**
	 * Key: longitudes
	 * @var string
	 */
	const LONGITUDES = 'lngs';
	
	/**
	 * Key: geohash
	 * @var string
	 */
	const GEOHASHES = 'geohashes';

	/**
	 * Key: elevations original
	 * @var string
	 */
	const ELEVATIONS_ORIGINAL = 'elevations_original';

	/**
	 * Key: elevations corrected
	 * @var string
	 */
	const ELEVATIONS_CORRECTED = 'elevations_corrected';

	/**
	 * Key: elevations source
	 * @var string
	 */
	const ELEVATIONS_SOURCE = 'elevations_source';

	/**
	 * Key: startpoint in geohash
	 * @var string
	 */
	const STARTPOINT = 'startpoint';
	
	/**
	 * Key: endpoint in geohash
	 * @var string
	 */
	const ENDPOINT = 'endpoint';

	/**
	 * Key: minimal longitude & latitude in geohash
	 * @var string
	 */
	const MIN = 'min';

	/**
	 * Key: maximal longitude & latitude in geohash
	 * @var string
	 */
	const MAX = 'max';	

	/**
	 * Key: in routenet
	 * @var string
	 */
	const IN_ROUTENET = 'in_routenet';

	/**
	 * Flag: ensure arrays to be equally sized
	 * @var bool
	 */
	protected $checkArraySizes = true;

	/**
	 * Check array sizes
	 * @throws \RuntimeException
	 */
	protected function checkArraySizes() {
		foreach ($this->properties() as $key) {
			if ($this->isArray($key)) {
				try {
					$count = count($this->Data[$key]);

					if ($key == self::ELEVATIONS_CORRECTED && $this->numberOfPoints > 0 && $count > $this->numberOfPoints) {
						$this->Data[$key] = array_slice($this->Data[$key], 0, $this->numberOfPoints);
					} else {
						$this->checkArraySize( $count );
					}
				} catch(\RuntimeException $E) {
					throw new \RuntimeException($E->getMessage().' (for '.$key.')');
				}
			}
		}
	}

	/**
	 * All properties
	 * @return array
	 */
	public static function allProperties() {
		return array(
			self::NAME,
			self::CITIES,
			self::DISTANCE,
			self::ELEVATION,
			self::ELEVATION_UP,
			self::ELEVATION_DOWN,
			self::GEOHASHES,
			self::LATITUDES,
			self::LONGITUDES,
			self::ELEVATIONS_ORIGINAL,
			self::ELEVATIONS_CORRECTED,
			self::ELEVATIONS_SOURCE,
			self::STARTPOINT,
			self::ENDPOINT,
			self::MIN,
			self::MAX,
			self::IN_ROUTENET
		);
	}
	
	/**
	 * All properties
	 * @return array
	 */
	public static function allDatabaseProperties() {
		return array(
			self::NAME,
			self::CITIES,
			self::DISTANCE,
			self::ELEVATION,
			self::ELEVATION_UP,
			self::ELEVATION_DOWN,
			self::GEOHASHES,
			self::ELEVATIONS_ORIGINAL,
			self::ELEVATIONS_CORRECTED,
			self::ELEVATIONS_SOURCE,
			self::STARTPOINT,
			self::ENDPOINT,
			self::MIN,
			self::MAX,
			self::IN_ROUTENET
		);
	}

	/**
	 * Properties
	 * @return array
	 */
	public function properties() {
		return static::allProperties();
	}

	/**
	 * Is the property an array?
	 * @param string $key
	 * @return bool
	 */
	public function isArray($key) {
		switch ($key) {
			case self::ELEVATIONS_ORIGINAL:
			case self::ELEVATIONS_CORRECTED:
			case self::LATITUDES:
			case self::LONGITUDES:
			case self::GEOHASHES:
				return true;
		}

		return false;
	}

	/**
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
		switch ($key) {
			case self::ELEVATIONS_ORIGINAL:
			case self::ELEVATIONS_CORRECTED:
			case self::GEOHASHES:
			case self::STARTPOINT:
			case self::ENDPOINT:
			case self::MIN:
			case self::MAX:
				return true;
		}
		return false;
	}

	/**
	 * Synchronize internal models
	 */
	public function synchronize() {
		parent::synchronize();

		$this->ensureAllNumericValues();
		//print_r($this->Data[self::LATITUDES]);
		if(!$this->hasGeohashes()) {
		    $this->setLatitudesLongitudes($this->Data[self::LATITUDES], $this->Data[self::LONGITUDES]);
		}
		
		$this->synchronizeStartAndEndpoint();

		if (!$this->hasCorrectedElevations()) {
			$this->set(self::ELEVATIONS_SOURCE, '');
		}
	}

	/**
	 * Ensure that numeric fields get numeric values
	 */
	protected function ensureAllNumericValues() {
		$this->ensureNumericValue(array(
			self::DISTANCE,
			self::ELEVATION,
			self::ELEVATION_UP,
			self::ELEVATION_DOWN,
			self::IN_ROUTENET
		));
	}

	public function setLatitudesLongitudes(array $latitudes, array $longitudes)
	{
	    $size = count($latitudes);
	    if ($size != count($longitudes)) {
		throw new \InvalidArgumentException('Latitude & Longitude Array cannot have different lenghts');
	    }
	    for ($i = 0; $i < $size; ++$i) {
		$Coordinate = new Coordinate($latitudes[$i].','.$longitudes[$i]);
		$geohashes[] = (new Geohash())->encode($Coordinate, 12)->getGeohash();
	    }
	    if ($size != count($geohashes)) {
		throw new \InvalidArgumentException('Geohash array cannot have different length than lat/lng');
	    } else {
		$this->Data[self::GEOHASHES] = $geohashes;
	    } 
	    
	    
	    /* set min, max, startpoint, endpoint */
	    $Latitudes = array_filter($latitudes);
	    $Longitudes = array_filter($longitudes);

	    if (!empty($Latitudes) && !empty($Longitudes)) {
		$StartpointCoordinate = new Coordinate(reset($Latitudes).','.reset($Longitudes));
		$this->Data[self::STARTPOINT] = (new Geohash())->encode($StartpointCoordinate, 10)->getGeohash();

		$EndpointCoordinate = new Coordinate(end($Latitudes).','.end($Longitudes));
		$this->Data[self::ENDPOINT] = (new Geohash())->encode($EndpointCoordinate, 10)->getGeohash();

		$MinCoordinate = new Coordinate(min($Latitudes).','.min($Longitudes));
		$this->Data[self::MIN] = (new Geohash())->encode($MinCoordinate, 10)->getGeohash();

		$MaxCoordinate = new Coordinate(max($Latitudes).','.max($Longitudes));
		$this->Data[self::MAX] = (new Geohash())->encode($MaxCoordinate, 10)->getGeohash();
	    }

	}
	
	/**
	 * Synchronize start- and endpoint
	 */
	protected function synchronizeStartAndEndpoint() {
	    //Todo Where to put?
		if (!$this->hasGeohashes()) {
			$this->Data[self::STARTPOINT] = null;
			$this->Data[self::ENDPOINT] = null;
			$this->Data[self::MIN] = null;
			$this->Data[self::MAX] = null;
		}
	}

	/**
	 * Number of points
	 * @return int
	 */
	public function num() {
		return $this->numberOfPoints;
	}

	/**
	 * Value at
	 * 
	 * Remark: This method may throw index offsets.
	 * @param int $index
	 * @param enum $key
	 * @return mixed
	 */
	public function at($index, $key) {
		return $this->Data[$key][$index];
	}

	/**
	 * Name
	 * @return string
	 */
	public function name() {
		return $this->Data[self::NAME];
	}

	/**
	 * Cities as array
	 * @return array
	 */
	public function citiesAsArray() {
		return explode(self::CITIES_SEPARATOR, $this->Data[self::CITIES]);
	}

	/**
	 * Distance
	 * @return float
	 */
	public function distance() {
		return $this->Data[self::DISTANCE];
	}

	/**
	 * Elevation
	 * @return int
	 */
	public function elevation() {
		return $this->Data[self::ELEVATION];
	}

	/**
	 * Elevation up
	 * @return int
	 */
	public function elevationUp() {
		return $this->Data[self::ELEVATION_UP];
	}

	/**
	 * Elevation down
	 * @return int
	 */
	public function elevationDown() {
		return $this->Data[self::ELEVATION_DOWN];
	}

	/**
	 * Latitudes
	 * @return array
	 */
	public function latitudes() {
		return $this->Data[self::LATITUDES];
	}

	/**
	 * Longitudes
	 * @return array
	 */
	public function longitudes() {
		return $this->Data[self::LONGITUDES];
	}
	
	/**
	 * Geohashes
	 * @return array
	 */
	public function geohashes() {
		return $this->Data[self::GEOHASHES];
	}


	/**
	 * Has position data?
	 * @return boolean
	 */
	public function hasPositionData() {
		return $this->has(self::GEOHASHES);
	}
	
	/**
	 * Has geohash data?
	 * @return boolean
	 */
	public function hasGeohashes() {
		return $this->has(self::GEOHASHES);
	}

	/**
	 * Original elevations
	 * @return array
	 */
	public function elevationsOriginal() {
		return $this->Data[self::ELEVATIONS_ORIGINAL];
	}

	/**
	 * Corrected elevations
	 * @return array
	 */
	public function elevationsCorrected() {
		return $this->Data[self::ELEVATIONS_CORRECTED];
	}

	/**
	 * Strategy name of elevation correction
	 * @return string
	 */
	public function elevationsSource() {
		return $this->Data[self::ELEVATIONS_SOURCE];
	}

	/**
	 * Elevations array (corrected or original)
	 * @return array
	 */
	public function elevations() {
		if ($this->hasCorrectedElevations()) {
			return $this->elevationsCorrected();
		} elseif ($this->hasOriginalElevations()) {
			return $this->elevationsOriginal();
		}

		return array();
	}

	/**
	 * @return boolean
	 */
	public function hasElevations() {
		return $this->hasOriginalElevations() || $this->hasCorrectedElevations();
	}

	/**
	 * @return boolean
	 */
	public function hasOriginalElevations() {
		return isset($this->Data[self::ELEVATIONS_ORIGINAL]) && !empty($this->Data[self::ELEVATIONS_ORIGINAL]);
	}

	/**
	 * @return boolean
	 */
	public function hasCorrectedElevations() {
		return isset($this->Data[self::ELEVATIONS_CORRECTED]) && !empty($this->Data[self::ELEVATIONS_CORRECTED]);
	}

	/**
	 * Is in routenet?
	 * @return boolean
	 */
	public function inRoutenet() {
		return ($this->Data[self::IN_ROUTENET] == 1);
	}

	/**
	 * Calculate distance between two coordinates
	 * @param double $lat1
	 * @param double $lon1
	 * @param double $lat2
	 * @param double $lon2
	 * @return double
	 */
	public static function gpsDistance($lat1, $lon1, $lat2, $lon2) {
		$rad1 = deg2rad($lat1);
		$rad2 = deg2rad($lat2);
		$dist = sin($rad1) * sin($rad2) +  cos($rad1) * cos($rad2) * cos(deg2rad($lon1 - $lon2)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;

		if (is_nan($miles))
			return 0;
	
		return ($miles * 1.609344);
	}
}