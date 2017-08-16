<?php
/**
 * This file contains class::Route
 * @package Runalyze\View\Leaflet
 */

namespace Runalyze\View\Leaflet;
use League\Geotools\Geotools;

/**
 * Leaflet route
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Leaflet
 */
class Route {
	/**
	 * ID
	 * @var string
	 */
	protected $id = '';

	/**
	 * JS-marker
	 * @var string[]
	 */
	protected $Marker = array();

	/**
	 * Paths
	 * @var array
	 */
	protected $Paths = array();

	/**
	 * Infos
	 *
	 * Information to be displayed on mouseover.
	 * First entry has to be distance.
	 *
	 * @var array
	 */
	protected $Infos = array();

	/**
	 * Labels for infos
	 *
	 * Labels for `$Infos`. Can be false to hide on mouseover.
	 *
	 * @var array
	 */
	protected $InfoLabels = array();

	/**
	 * Functions to style info
	 * @var array
	 */
	protected $InfoFunctions = array();

	/**
	 * Options
	 * @var array
	 */
	protected $Options = array();

	/**
	 * Construct new route
	 * @param string $id
	 */
	public function __construct($id) {
		$this->id = $id;
	}

	/**
	 * ID
	 * @return int
	 */
	final public function id() {
		return $this->id;
	}

    /**
     * @return bool
     */
	public function isEmpty() {
	    return empty($this->Paths);
    }

	/**
	 * Get JS
	 * @return string
	 */
	final public function js() {
		$Options = 'segments: '.json_encode($this->Paths);

		if (count($this->Paths) == count($this->Infos)) {
			$Options .= ', segmentsInfoLabels: '.json_encode($this->InfoLabels);
			$Options .= ', segmentsInfo: '.json_encode($this->Infos);
			$Options .= ', segmentsInfoFunctions: ['.implode(',', $this->InfoFunctions).']';
		}

		$Options .= ', markertopush: ['.implode(',', $this->Marker).']';

		foreach ($this->Options as $property => $value) {
			$Options .= ', '.$property.': '.json_encode($value);
		}

		return 'RunalyzeLeaflet.Routes.addRoute(\''.$this->id.'\', {'.$Options.'});';
	}

	/**
	 * Add option
	 * @param string $property
	 * @param mixed $value
	 */
	final public function addOption($property, $value) {
		$this->Options[$property] = $value;
	}

	/**
	 * Add segment
	 *
	 * Caution: Set infos for all paths or for none!
	 *
	 * @param array $Path
	 * @param array $Info [optional] must be of the same size as $Path
	 */
	final public function addSegment(array $Path, array $Info = array()) {
		if (empty($Path))
			return;

		$this->Paths[] = $Path;

		if (!empty($Info))
			$this->Infos[] = $Info;
	}

	/**
	 * Add marker
	 * @param float $Lat
	 * @param float $Lng
	 * @param string $Icon JS-icon
	 * @param string $Tooltip [optional]
	 */
	final public function addMarker($Lat, $Lng, $Icon, $Tooltip = '') {
		$this->Marker[] = 'L.marker(['.$Lat.','.$Lng.'], {icon: '.$Icon.', tooltip: '.json_encode($Tooltip).'})';
	}

	/**
	 * Add marker
	 * @param string $geohash
	 * @param string $Icon JS-icon
	 * @param string $Tooltip [optional]
	 */
	final public function addMarkerGeohash($geohash, $Icon, $Tooltip = '') {
	    if ('7zzzzzzzzzzz' == $geohash) {
	        return;
        }

		$coordinate = (new Geotools())->geohash()->decode($geohash)->getCoordinate();

		$this->Marker[] = 'L.marker(['.$coordinate->getLatitude().','.$coordinate->getLongitude().'], {icon: '.$Icon.', tooltip: '.json_encode($Tooltip).'})';
	}

	/**
	 * Dist icon
	 * @param int $km
	 * @return string
	 */
	final public function distIcon($km) {
		return 'RunalyzeLeaflet.Routes.distIcon('.(int)$km.')';
	}

	/**
	 * Start icon
	 * @return string
	 */
	final public function startIcon() {
		return 'RunalyzeLeaflet.Routes.startIcon()';
	}

	/**
	 * End icon
	 * @return string
	 */
	final public function endIcon() {
		return 'RunalyzeLeaflet.Routes.endIcon()';
	}

	/**
	 * Pause icon
	 * @return string
	 */
	final public function pauseIcon() {
		return 'RunalyzeLeaflet.Routes.pauseIcon()';
	}
}
