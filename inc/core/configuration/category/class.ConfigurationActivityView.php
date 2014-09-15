<?php
/**
 * This file contains class::ConfigurationActivityView
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Activity view
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationActivityView extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'activity-view';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createMapOptions();
		$this->createPlotOptions();
		$this->createPacePlotOptions();
		$this->createOtherOptions();
	}

	/**
	 * Create: map options
	 * - GMAP_PATH_PRECISION
	 * - GMAP_PATH_BREAK
	 * - TRAINING_MAP_COLOR
	 * - TRAINING_LEAFLET_LAYER
	 */
	protected function createMapOptions() {
		$this->createHandle('GMAP_PATH_PRECISION', new ActivityRoutePrecision());
		$this->createHandle('GMAP_PATH_BREAK', new ActivityRouteBreak());

		// TODO
		// - use in Leaflet (is currently only used for ExporterKML)
		$this->createHandle('TRAINING_MAP_COLOR', new ParameterString('#FF5500'));

		$this->createHandle('TRAINING_LEAFLET_LAYER', new ParameterString('OpenStreetMap'));
	}

	/**
	 * Route precision
	 * @return ActivityRoutePrecision
	 */
	public function routePrecision() {
		return $this->object('GMAP_PATH_PRECISION');
	}

	/**
	 * Route break
	 * @return ActivityRouteBreak
	 */
	public function routeBreak() {
		return $this->object('GMAP_PATH_BREAK');
	}

	/**
	 * Route color
	 * @return string
	 */
	public function routeColor() {
		return $this->get('TRAINING_MAP_COLOR');
	}

	/**
	 * Map layer
	 * @return string
	 */
	public function mapLayer() {
		return $this->get('TRAINING_LEAFLET_LAYER');
	}

	/**
	 * Create: Plot options
	 * - TRAINING_PLOT_SMOOTH
	 * - TRAINING_PLOT_MODE
	 * - TRAINING_PLOT_PRECISION
	 */
	protected function createPlotOptions() {
		$this->createHandle('TRAINING_PLOT_SMOOTH', new ParameterBool(false));

		$this->createHandle('TRAINING_PLOT_MODE', new ActivityPlotMode());
		$this->createHandle('TRAINING_PLOT_PRECISION', new ActivityPlotPrecision());
	}

	/**
	 * Main sport
	 * @return bool
	 */
	public function smoothCurves() {
		return $this->get('TRAINING_PLOT_SMOOTH');
	}

	/**
	 * Plot mode
	 * @return ActivityPlotMode
	 */
	public function plotMode() {
		return $this->object('TRAINING_PLOT_MODE');
	}

	/**
	 * Plot precision
	 * @return ActivityPlotPrecision
	 */
	public function plotPrecision() {
		return $this->object('TRAINING_PLOT_PRECISION');
	}

	/**
	 * Create: Plot options for pace
	 * - PACE_Y_LIMIT_MAX
	 * - PACE_Y_LIMIT_MIN
	 * - PACE_Y_AXIS_REVERSE
	 * - PACE_HIDE_OUTLIERS
	 */
	protected function createPacePlotOptions() {
		$this->createHandle('PACE_Y_LIMIT_MIN', new PaceAxisMinimum());
		$this->createHandle('PACE_Y_LIMIT_MAX', new PaceAxisMaximum());

		$this->createHandle('PACE_Y_AXIS_REVERSE', new ParameterBool(false));

		$this->createHandle('PACE_HIDE_OUTLIERS', new ParameterBool(false));
	}

	/**
	 * Pace: Y axis minimum
	 * @return PaceAxisMinimum
	 */
	public function paceYaxisMinimum() {
		return $this->object('PACE_Y_LIMIT_MIN');
	}

	/**
	 * Pace: Y axis maximum
	 * @return PaceAxisMaximum
	 */
	public function paceYaxisMaximum() {
		return $this->object('PACE_Y_LIMIT_MAX');
	}

	/**
	 * Reverse pace axis
	 * @return bool
	 */
	public function reversePaceAxis() {
		return $this->get('PACE_Y_AXIS_REVERSE');
	}

	/**
	 * Ignore pace outliers
	 * @return bool
	 */
	public function ignorePaceOutliers() {
		return $this->get('PACE_HIDE_OUTLIERS');
	}

	/**
	 * Create: Plot options
	 * - TRAINING_DECIMALS
	 * - ELEVATION_METHOD
	 * - ELEVATION_MIN_DIFF
	 */
	protected function createOtherOptions() {
		$this->createHandle('TRAINING_DECIMALS', new ParameterSelect('1', array(
			'options'		=> array('0', '1', '2')
		)));

		$this->createHandle('ELEVATION_METHOD', new ElevationMethod());

		$this->createHandle('ELEVATION_MIN_DIFF', new ParameterInt(3));
	}

	/**
	 * Decimals to display
	 * @return int
	 */
	public function decimals() {
		return (int)$this->get('TRAINING_DECIMALS');
	}

	/**
	 * Elevation method
	 * @return ElevationMethod
	 */
	public function elevationMethod() {
		return $this->object('ELEVATION_METHOD');
	}

	/**
	 * Elevation min diff
	 * @return int
	 */
	public function elevationMinDiff() {
		return $this->get('ELEVATION_MIN_DIFF');
	}
}