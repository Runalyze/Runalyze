<?php
/**
 * This file contains class::Map
 * @package Runalyze\View\Leaflet
 */

namespace Runalyze\View\Leaflet;

use Runalyze\View;
use Runalyze\Configuration;

/**
 * Leaflet map
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Leaflet
 */
class Map extends View\Object {
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
	 * @var \Runalyze\View\Leaflet\Route[]
	 */
	protected $Routes = array();

	/**
	 * Boundaries
	 * @var array
	 */
	protected $Bounds = array();

	/**
	 * Construct new map
	 * @param string $containerID
	 * @param int $height [optional]
	 * @param int $width [optional]
	 */
	public function __construct($containerID, $height = 0, $width = 0) {
		$this->id = $containerID;
		$this->height = $height;
		$this->width = $width;
	}

	/**
	 * Add route
	 * @param \Runalyze\View\Leaflet\Route $route
	 */
	public function addRoute(Route $route) {
		$this->Routes[] = $route;
	}

	/**
	 * Set bounds
	 * @param array $bounds
	 */
	public function setBounds(array $bounds) {
		$this->Bounds = $bounds;
	}

	/**
	 * Get code
	 * @return string
	 */
	public function code() {
		return $this->html().$this->js();
	}

	/**
	 * Get HTML
	 * @return string
	 */
	public function html() {
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
	public function js() {
		$Code  = 'RunalyzeLeaflet.setDefaultLayer("'.Configuration::ActivityView()->mapLayer().'");';
		$Code .= 'RunalyzeLeaflet.init(\''.$this->id.'\', { scrollWheelZoom: false} );';

		foreach ($this->Routes as $Route) {
			$Code .= $Route->js();
		}

		if (!empty($this->Bounds)) {
			$Code .= 'RunalyzeLeaflet.map().fitBounds([['.$this->Bounds['lat.min'].','.$this->Bounds['lng.min'].'],['.$this->Bounds['lat.max'].','.$this->Bounds['lng.max'].']]);';
		}

		if (!empty($this->Routes)) {
	        $Code.= 'RunalyzeLeaflet.Routes.routeid="'.$this->Routes[0]->id().'";';
		}

		return '<script>'.$Code.'</script>';
	}
}