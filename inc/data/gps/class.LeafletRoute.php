<?php
/**
 * This file contains class::LeafletRoute
 * @package Runalyze\Data\GPS
 */
/**
 * Leaflet-route
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS
 */
class LeafletRoute {
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
	 * @var array
	 */
	protected $Infos = array();

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
	 * Get JS
	 * @return string
	 */
	final public function getJS() {
		$Options = 'segments: '.json_encode($this->Paths);

		if (count($this->Paths) == count($this->Infos))
			$Options .= ', segmentsInfo: '.json_encode($this->Infos);

		$Options .= ', markertopush: ['.implode(',', $this->Marker).']';

		foreach ($this->Options as $property => $value)
			$Options .= ', '.$property.': '.json_encode($value);

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
		$this->Marker[] = 'L.marker(['.$Lat.','.$Lng.'], {icon: '.$Icon.', tooltip: "'.$Tooltip.'"})';
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
}