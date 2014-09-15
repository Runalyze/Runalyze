<?php
/**
 * This file contains class::ConfigurationActivityView
 * @package Runalyze\System\Configuration\Category
 */
/**
 * Configuration category: Activity view
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Category
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
	 * Title
	 * @return string
	 */
	public function title() {
		return __('Activity view');
	}

	/**
	 * Create values
	 */
	protected function createValues() {
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
		$this->createValue(new ActivityRoutePrecision('GMAP_PATH_PRECISION'));
		$this->createValue(new ActivityRouteBreak('GMAP_PATH_BREAK'));

		// TODO
		// - use in Leaflet (is currently only used for ExporterKML)
		$this->createValue(new ConfigurationValueString('TRAINING_MAP_COLOR', array(
			'default'		=> '#FF5500',
			'label'			=> __('Map: line color'),
			'tooltip'		=> __('as #RGB code'),
			'onchange'		=> Ajax::$RELOAD_TRAINING
		)));

		$this->createValue(new ConfigurationValueString('TRAINING_LEAFLET_LAYER', array(
			'default'		=> 'OpenStreetMap',
			'label'			=> __('Map: layer')
		)));
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
		$this->createValue(new ConfigurationValueBool('TRAINING_PLOT_SMOOTH', array(
			'default'		=> false,
			'label'			=> __('Plot: smooth curves'),
			'onchange'		=> Ajax::$RELOAD_TRAINING
		)));

		$this->createValue(new ActivityPlotMode('TRAINING_PLOT_MODE'));
		$this->createValue(new ActivityPlotPrecision('TRAINING_PLOT_PRECISION'));
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
		$this->createValue(new PaceAxisMinimum('PACE_Y_LIMIT_MIN'));
		$this->createValue(new PaceAxisMaximum('PACE_Y_LIMIT_MAX'));

		$this->createValue(new ConfigurationValueBool('PACE_Y_AXIS_REVERSE', array(
			'default'		=> false,
			'label'			=> __('Pace: Reverse y-axis'),
			'tooltip'		=> __('Reverse the y-axis such that a faster pace is at the top.'),
			'onchange'		=> Ajax::$RELOAD_TRAINING
		)));

		$this->createValue(new ConfigurationValueBool('PACE_HIDE_OUTLIERS', array(
			'default'		=> false,
			'label'			=> __('Pace: Ignore outliers'),
			'tooltip'		=> __('Try to ignore outliers in the pace plot.'),
			'onchange'		=> Ajax::$RELOAD_TRAINING
		)));
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
		$this->createValue(new ConfigurationValueSelect('TRAINING_DECIMALS', array(
			'default'		=> '1',
			'label'			=> __('Number of decimals'),
			'options'		=> array('0', '1', '2'),
			'onchange'		=> Ajax::$RELOAD_DATABROWSER_AND_TRAINING
		)));

		$this->createValue(new ElevationMethod('ELEVATION_METHOD'));

		$this->createValue(new ConfigurationValueInt('ELEVATION_MIN_DIFF', array(
			'default'		=> 3,
			'label'			=> __('Elevation: threshold'),
			'tooltip'		=> __('Treshold for the weeding algorithm'),
			'unit'			=> FormularUnit::$M,
			'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate elevation values."));'
		)));
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