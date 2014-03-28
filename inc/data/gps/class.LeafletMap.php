<?php
/**
 * This file contains class::LeafletMap
 * @package Runalyze\Data\GPS
 */
/**
 * Leaflet-map
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS
 */
class LeafletMap {
	/**
	 * HTML id
	 * @var string
	 */
	protected $id = '';

	/**
	 * Height
	 * @var int
	 */
	protected $height = 0;

	/**
	 * Width
	 * @var int
	 */
	protected $width = 0;

	/**
	 * Routes
	 * @var LeafletRoute[]
	 */
	protected $Routes = array();

	/**
	 * Bounds
	 * @var array
	 */
	protected $Bounds = array();

	/**
	 * Construct new map
	 * @param string $ContainerID
	 * @param int $height [optional]
	 * @param int $width [optional]
	 */
	public function __construct($ContainerID, $height = 0, $width = 0) {
		$this->id = $ContainerID;
		$this->height = $height;
		$this->width = $width;
	}

	/**
	 * Add route
	 * @param LeafletRoute $Route
	 */
	public function addRoute(LeafletRoute $Route) {
		$this->Routes[] = $Route;
	}

	/**
	 * Set bounds
	 * @param array $Bounds
	 */
	public function setBounds(array $Bounds) {
		$this->Bounds = $Bounds;
	}

	/**
	 * Display map with
	 * 
	 * Outputs html container and javascript
	 */
	public function display() {
		echo $this->getCode();
	}

	/**
	 * Get code
	 * @return string
	 */
	public function getCode() {
		return $this->getHTML().$this->getJS();
	}

	/**
	 * Get HTML
	 * @return string
	 */
	public function getHTML() {
		$style = '';

		if ($this->height > 0)
			$style .= 'height:'.(int)$this->height.'px;';

		if ($this->width > 0)
			$style .= 'width:'.(int)$this->width.'px;';

		if ($style != '')
			$style = ' style="'.$style.'"';

		return '<div id="'.$this->id.'" class="map"'.$style.'></div>';
	}

	/**
	 * Get JS
	 * @return string
	 */
	public function getJS() {
		$Code  = 'RunalyzeLeaflet.setDefaultLayer("'.CONF_TRAINING_MAPTYPE.'");';
		$Code .= 'RunalyzeLeaflet.init(\''.$this->id.'\');';

		foreach ($this->Routes as $Route)
			$Code .= $Route->getJS();

		if (!empty($this->Bounds))
			$Code .= 'RunalyzeLeaflet.map().fitBounds([['.$this->Bounds['lat.min'].','.$this->Bounds['lng.min'].'],['.$this->Bounds['lat.max'].','.$this->Bounds['lng.max'].']]);';

		return '<script>'.$Code.'</script>';
	}
}