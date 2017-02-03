<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Route
 */

namespace Runalyze\Model\Route;

use Runalyze\Model;
use League\Geotools\Geohash\Geohash;
use \League\Geotools\Coordinate\Coordinate;
use Runalyze\Calculation\Route\GeohashLine;

/**
 * Route entity
 *
 * Attention: `set(Entity::GEOHASHES, $geohashes)` or `setLatitudesLongitudes($lats, $lngs)`
 * should be used instead of serving geohashes in constructor to ensure correct
 * min/max geohashes. They are not calculated within `synchronize()`!
 *
 * @author Hannes Christiansen
 * @package Runalyze\Model\Route
 */
class Entity extends Model\EntityWithID implements Model\Loopable {
	/**
	 * Cities separator
	 * @var string
	 */
	const CITIES_SEPARATOR = ' - ';

	/**
	 * @var int
	 */
	const PATH_GEOHASH_PRECISION = 12;

	/**
	 * @var int
	 */
	const BOUNDARIES_GEOHASH_PRECISION = 10;

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
     * Key: lock
     * @var string
     */
    const LOCK = 'lock';

	/**
	 * Flag: ensure arrays to be equally sized
	 * @var bool
	 */
	protected $checkArraySizes = true;

    /**
     * Construct
     * @param array $data
     */
    public function __construct(array $data = array()) {
        parent::__construct($data);

        if ($this->hasGeohashes() && !empty($this->Data[self::GEOHASHES] )) {
            $this->Data[self::GEOHASHES] = GeohashLine::extend($this->Data[self::GEOHASHES]);
        }
    }

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
			self::ELEVATIONS_ORIGINAL,
			self::ELEVATIONS_CORRECTED,
			self::ELEVATIONS_SOURCE,
			self::STARTPOINT,
			self::ENDPOINT,
			self::MIN,
			self::MAX,
			self::IN_ROUTENET,
            self::LOCK
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
		$this->synchronizeStartAndEndpoint();
        $this->ensureAllNullValues();

		if (!$this->hasCorrectedElevations()) {
			$this->set(self::ELEVATIONS_SOURCE, '');
		}
	}

	protected function ensureAllNullValues() {
        $this->ensureNullIfEmpty(self::ELEVATIONS_ORIGINAL);
        $this->ensureNullIfEmpty(self::ELEVATIONS_CORRECTED);
        $this->ensureNullIfEmpty(self::GEOHASHES);
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

	/**
	 * Set array
	 * @param string $key
	 * @param mixed $value
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public function set($key, $value) {
		parent::set($key, $value);

        if ($key == self::GEOHASHES) {
            $this->setMinMaxFromGeohashes($value);
        }
	}

    /**
     * @param array $geohashes
     */
    public function setGeohashesWithoutMinMaxRecalculation(array $geohashes)
    {
        $this->Data[self::GEOHASHES] = $geohashes;
    }

	/**
	 * @param array $geohashes
	 */
	public function setMinMaxFromGeohashes(array $geohashes) {
		$latitudes = array();
		$longitudes = array();

		foreach ($geohashes as $geohash) {
			$coordinate = (new Geohash())->decode($geohash)->getCoordinate();
			$latitudes[] = round($coordinate->getLatitude(), 6);
			$longitudes[] = round($coordinate->getLongitude(), 6);
		}

		$this->setMinMaxFromLatitudesLongitudes($latitudes, $longitudes);
	}

	public function forceToSetMinMaxFromGeohashes() {
	    $this->setMinMaxFromGeohashes($this->Data[self::GEOHASHES]);
	}

	/**
	 * Set geohashes from latitudes/longitudes
	 * @param array $latitudes
	 * @param array $longitudes
	 * @throws \InvalidArgumentException
	 */
	public function setLatitudesLongitudes(array $latitudes, array $longitudes) {
		$size = count($latitudes);

		if ($size != count($longitudes)) {
			throw new \InvalidArgumentException('Latitude & Longitude Array cannot have different lenghts');
		}

		$latitudes = array_map(function ($value) { return ($value == '') ? 0 : (double)$value; }, $latitudes);
		$longitudes = array_map(function ($value) { return ($value == '') ? 0 : (double)$value; }, $longitudes);

		$geohashes = array();

		for ($i = 0; $i < $size; ++$i) {
			$geohashes[] = (new Geohash())->encode(new Coordinate(array($latitudes[$i], $longitudes[$i])), self::PATH_GEOHASH_PRECISION)->getGeohash();
		}

		$this->Data[self::GEOHASHES] = $geohashes;
		$this->handleNewArraySize($size);

		$this->setMinMaxFromLatitudesLongitudes($latitudes, $longitudes);
	}

	/**
	 * create latitude and longitude array
	 * @return array Array with coordiantes: ['lat' => array, 'lng' => array]
	 */
	public function latitudesAndLongitudesFromGeohash() {
		$Coordinates = array();
		$Geohashes = $this->Data[self::GEOHASHES];
		$size = count($this->Data[self::GEOHASHES]);

		for ($i = 0; $i < $size; $i++) {
			$geo = (new Geohash())->decode($Geohashes[$i])->getCoordinate();
			$Coordinates['lat'][] = round($geo->getLatitude(), 6);
			$Coordinates['lng'][] = round($geo->getLongitude(), 6);
		}

		return $Coordinates;
	}

	/**
	 * get latitudes array from geohashes
	 * @return array latitudes
	 */
	public function latitudesFromGeohash() {
		return $this->latitudesAndLongitudesFromGeohash()['lat'];
	}

	/**
	 * get longitudes array from geohashes
	 * @return array longitudes
	 */
	public function longitudesFromGeohash() {
		return $this->latitudesAndLongitudesFromGeohash()['lng'];
	}

	/**
	 * @param array $latitudes
	 * @param array $longitudes
	 */
	protected function setMinMaxFromLatitudesLongitudes(array $latitudes, array $longitudes) {
		$latitudes = array_filter($latitudes);
		$longitudes = array_filter($longitudes);

		if (!empty($latitudes) && !empty($longitudes)) {
			$MinCoordinate = new Coordinate(array(min($latitudes), min($longitudes)));
			$this->Data[self::MIN] = (new Geohash())->encode($MinCoordinate, self::BOUNDARIES_GEOHASH_PRECISION)->getGeohash();

			$MaxCoordinate = new Coordinate(array(max($latitudes), max($longitudes)));
			$this->Data[self::MAX] = (new Geohash())->encode($MaxCoordinate, self::BOUNDARIES_GEOHASH_PRECISION)->getGeohash();
		} else {
			$this->setMinMaxToNull();
		}
	}

	/**
	 * Set min/max to null
	 */
	protected function setMinMaxToNull() {
		$this->Data[self::MIN] = null;
		$this->Data[self::MAX] = null;
	}

	/**
	 * Synchronize start- and endpoint
	 */
	protected function synchronizeStartAndEndpoint() {
		$this->Data[self::STARTPOINT] = $this->findStartpoint();
		$this->Data[self::ENDPOINT] = $this->findEndpoint();

		if (null === $this->Data[self::STARTPOINT]) {
			$this->Data[self::GEOHASHES] = array();
		}
	}

	/**
	 * @return string|null
	 */
	protected function findStartpoint() {
		return $this->findFirstNonNullGeohash($this->Data[self::GEOHASHES]);
	}

	/**
	 * @return string|null
	 */
	protected function findEndpoint() {
		return $this->findFirstNonNullGeohash(array_reverse($this->Data[self::GEOHASHES]));
	}

	/**
	 * @param array $geohashes
	 * @return null|string
	 */
	protected function findFirstNonNullGeohash(array $geohashes) {
		$nullGeohash = (new Geohash())->encode(new Coordinate(array(0, 0)), self::BOUNDARIES_GEOHASH_PRECISION)->getGeohash();

		foreach ($geohashes as $geohash) {
			$geohash = substr($geohash, 0, self::BOUNDARIES_GEOHASH_PRECISION);

			if ($geohash != $nullGeohash) {
				return $geohash;
			}
		}

		return null;
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
	 * @param string $key
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
