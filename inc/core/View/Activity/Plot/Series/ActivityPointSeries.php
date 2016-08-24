<?php
/**
 * This file contains class::ActivityPointSeries
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use \Plot;

/**
 * Activity series with points
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
abstract class ActivityPointSeries extends ActivitySeries {
	const COLOR_PERFECT = '#8E44AD';
	const COLOR_GREAT = '#2980B9';
	const COLOR_GOOD = '#27AE60';
	const COLOR_OKAY = '#E67E22';
	const COLOR_BAD = '#E74C3C';

	/**
	 * @var int
	 */
	protected $PointSize = 2;

	/**
	 * Add to plot
	 * @param \Plot $Plot
	 * @param int $yAxis
	 * @param boolean $addAnnotations [optional]
	 */
	public function addTo(Plot $Plot, $yAxis, $addAnnotations = true) {
		parent::addTo($Plot, $yAxis, $addAnnotations);

		$Plot->showPoints($this->PointSize);
		$Plot->smoothing(false);
	}

	/**
	 * Set thresholds for point colors
	 * @param Plot $Plot
	 * @param float $perfect points above this limit will be colored as 'perfect'
	 * @param float $great points above this limit will be colored as 'great'
	 * @param float $good points above this limit will be colored as 'good'
	 * @param float $okay points above this limit will be colored as 'okay'
	 */
	protected function setColorThresholdsAbove(Plot $Plot, $perfect, $great, $good, $okay) {
		$Plot->Options['hooks']['draw'] = array('RunalyzePlot.flotHookColorPoints('
			. '['.$perfect.', '.$great.', '.$good.', '.$okay.'], '
			. '["'.self::COLOR_PERFECT.'", "'.self::COLOR_GREAT.'", "'.self::COLOR_GOOD.'", "'.self::COLOR_OKAY.'"], '
			. '"'.self::COLOR_BAD.'")'
		);
	}

	/**
	 * Set thresholds for point colors
	 * @param Plot $Plot
	 * @param float $perfect points below this limit will be colored as 'perfect'
	 * @param float $great points below this limit will be colored as 'great'
	 * @param float $good points below this limit will be colored as 'good'
	 * @param float $okay points below this limit will be colored as 'okay'
	 */
	protected function setColorThresholdsBelow(Plot $Plot, $perfect, $great, $good, $okay) {
		$Plot->Options['hooks']['draw'] = array('RunalyzePlot.flotHookColorPoints('
			. '['.$okay.', '.$good.', '.$great.', '.$perfect.'], '
			. '["'.self::COLOR_BAD.'", "'.self::COLOR_OKAY.'", "'.self::COLOR_GOOD.'", "'.self::COLOR_GREAT.'"], '
			. '"'.self::COLOR_PERFECT.'")'
		);
	}
}
