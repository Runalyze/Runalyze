<?php
/**
 * This file contains class::ActivityView
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\Bool;
use Runalyze\Parameter\Int;
use Runalyze\Parameter\String;
use Runalyze\Parameter\Select;
use Runalyze\Parameter\Application\ActivityRoutePrecision;
use Runalyze\Parameter\Application\ActivityRouteBreak;
use Runalyze\Parameter\Application\ActivityPlotMode;
use Runalyze\Parameter\Application\ActivityPlotPrecision;
use Runalyze\Parameter\Application\PaceAxisMinimum;
use Runalyze\Parameter\Application\PaceAxisMaximum;
use Runalyze\Parameter\Application\ElevationMethod;
use Ajax;

/**
 * Configuration category: Activity view
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ActivityView extends \Runalyze\Configuration\Category {
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
		$this->createHandle('TRAINING_MAP_COLOR', new String('#FF5500'));

		$this->createHandle('TRAINING_LEAFLET_LAYER', new String('OpenStreetMap'));
		$this->createHandle('TRAINING_MAP_SHOW_FIRST', new Bool(false));
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
	 * Show map first
	 * @return string
	 */
	public function mapFirst() {
		return $this->get('TRAINING_MAP_SHOW_FIRST');
	}

	/**
	 * Map layer
	 * @return string
	 */
	public function mapLayer() {
		return $this->get('TRAINING_LEAFLET_LAYER');
	}

	/**
	 * Update layer
	 * @param string $layer
	 */
	public function updateLayer($layer) {
		$this->object('TRAINING_LEAFLET_LAYER')->set($layer);
		$this->updateValue( $this->handle('TRAINING_LEAFLET_LAYER') );
	}

	/**
	 * Create: Plot options
	 * - TRAINING_PLOT_SMOOTH
	 * - TRAINING_PLOT_MODE
	 * - TRAINING_PLOT_PRECISION
	 */
	protected function createPlotOptions() {
		$this->createHandle('TRAINING_PLOT_SMOOTH', new Bool(false));

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

		$this->createHandle('PACE_Y_AXIS_REVERSE', new Bool(false));

		$this->createHandle('PACE_HIDE_OUTLIERS', new Bool(false));
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
	 * - ELEVATION_TRESHOLD
	 */
	protected function createOtherOptions() {
		$this->createHandle('TRAINING_DECIMALS', new Select('1', array(
			'options'		=> array('0', '1', '2')
		)));

		$this->createHandle('ELEVATION_METHOD', new ElevationMethod());

		$this->createHandle('ELEVATION_TRESHOLD', new Int(3));
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
		return $this->get('ELEVATION_TRESHOLD');
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('TRAINING_PLOT_MODE')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER_AND_TRAINING);
		$this->handle('TRAINING_MAP_SHOW_FIRST')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);

		$this->handle('TRAINING_PLOT_MODE')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);
		$this->handle('TRAINING_PLOT_SMOOTH')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);
		$this->handle('PACE_Y_LIMIT_MIN')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);
		$this->handle('PACE_Y_LIMIT_MAX')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);
		$this->handle('PACE_Y_AXIS_REVERSE')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);
		$this->handle('PACE_HIDE_OUTLIERS')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);

		$this->handle('TRAINING_PLOT_PRECISION')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);

		$this->handle('GMAP_PATH_BREAK')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);
		$this->handle('GMAP_PATH_PRECISION')->registerOnchangeFlag(Ajax::$RELOAD_TRAINING);

		$this->handle('ELEVATION_METHOD')->registerOnchangeEvent('Runalyze\\Configuration\\Messages::useCleanup');
		$this->handle('ELEVATION_TRESHOLD')->registerOnchangeEvent('Runalyze\\Configuration\\Messages::useCleanup');
	}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('Activity view') );

		$Fieldset->addHandle( $this->handle('TRAINING_DECIMALS'), array(
			'label'		=> __('Number of decimals')
		));

		$this->addHandlesForMapsTo($Fieldset);
		$this->addHandlesForPlotsTo($Fieldset);
		$this->addHandlesForElevationTo($Fieldset);

		return $Fieldset;
	}

	/**
	 * Add handles for plots to fieldset
	 * @param \Runalyze\Configuration\Fieldset $Fieldset
	 */
	private function addHandlesForPlotsTo(Fieldset &$Fieldset) {
		$Fieldset->addHandle( $this->handle('TRAINING_PLOT_MODE'), array(
			'label'		=> __('Plots: combination')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_PLOT_SMOOTH'), array(
			'label'		=> __('Plots: smooth curves')
		));

        $Fieldset->addHandle( $this->handle('TRAINING_PLOT_PRECISION'), array(
            'label'		=> __('Plots: precision'),
            'tooltip'	=> __('How many data points should be plotted?')
        ));

		$Fieldset->addHandle( $this->handle('PACE_Y_LIMIT_MIN'), array(
			'label'		=> __('Pace plot: y-axis minimum'),
			'tooltip'	=> __('Data points below this limit will be ignored. (only for running)')
		));

		$Fieldset->addHandle( $this->handle('PACE_Y_LIMIT_MAX'), array(
			'label'		=> __('Pace plot: y-axis maximum'),
			'tooltip'	=> __('Data points above this limit will be ignored. (only for running)')
		));

		$Fieldset->addHandle( $this->handle('PACE_Y_AXIS_REVERSE'), array(
			'label'		=> __('Pace: Reverse y-axis'),
			'tooltip'	=> __('Reverse the y-axis such that a faster pace is at the top.')
		));

		$Fieldset->addHandle( $this->handle('PACE_HIDE_OUTLIERS'), array(
			'label'		=> __('Pace: Ignore outliers'),
			'tooltip'	=> __('Try to ignore outliers in the pace plot.')
		));
	}

	/**
	 * Add handles for maps to fieldset
	 * @param \Runalyze\Configuration\Fieldset $Fieldset
	 */
	private function addHandlesForMapsTo(Fieldset &$Fieldset) {
		$Fieldset->addHandle( $this->handle('GMAP_PATH_BREAK'), array(
			'label'		=> __('Map: interrupt route'),
			'tooltip'	=> __('The gps path can be interrupted in case of <em>interruptions</em> (e.g. by car/train/...).'.
						'Finding these interruptions is not easy. You can define up to which distance (in seconds by average pace)'.
						'between two data points can be interpolated.')
		));

		$Fieldset->addHandle( $this->handle('GMAP_PATH_PRECISION'), array(
			'label'		=> __('Map: precision'),
			'tooltip'	=> __('How many data points shoud be displayed?')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_MAP_COLOR'), array(
			'label'		=> __('Map: line color'),
			'tooltip'	=> __('as hexadecimal rgb (e.g. #ff5500)')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_MAP_SHOW_FIRST'), array(
			'label'		=> __('Map: show map first'),
			'tooltip'	=> __('show map before plots')
		));
	}

	/**
	 * Add handles for elevation to fieldset
	 * @param \Runalyze\Configuration\Fieldset $Fieldset
	 */
	private function addHandlesForElevationTo(Fieldset &$Fieldset) {
		$Fieldset->addHandle( $this->handle('ELEVATION_METHOD'), array(
			'label'		=> __('Elevation: smoothing'),
			'tooltip'	=> __('Choose the algorithm to smooth the elevation data')
		));

		$Fieldset->addHandle( $this->handle('ELEVATION_TRESHOLD'), array(
			'label'		=> __('Elevation: threshold'),
			'tooltip'	=> __('Threshold for the smoothing algorithm')
		));
	}
}