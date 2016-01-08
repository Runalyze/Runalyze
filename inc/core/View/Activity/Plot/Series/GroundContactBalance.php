<?php
/**
 * This file contains class::GroundContactBalance
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

use Plot;

/**
 * Plot for: ground contact balance
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class GroundContactBalance extends ActivityPointSeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(200,100,0)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::GROUNDCONTACT_BALANCE);
		$this->setManualAverage($context->activity()->groundContactBalance()/100);
		$this->manipulateData();
	}

	/**
	 * Manipulate data
	 */
	protected function manipulateData() {
		$this->Data = array_map(array($this, 'correctUnit'), $this->Data);
		$this->Data = array_filter($this->Data);
	}

	/**
	 * Change value by internal factor
	 * @param int $value
	 * @return float
	 */
	protected function correctUnit($value) {
		return 0.01*$value;
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Ground contact balance');
		$this->Color = self::COLOR;

		$this->UnitString = '%';
		$this->UnitDecimals = 1;

		$this->TickSize = 0.2;
		$this->TickDecimals = 1;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

	/**
	 * Add to plot
	 * @param \Plot $Plot
	 * @param int $yAxis
	 * @param boolean $addAnnotations [optional]
	 */
	public function addTo(Plot &$Plot, $yAxis, $addAnnotations = true) {
		parent::addTo($Plot, $yAxis, $addAnnotations);

		$Plot->Options['hooks']['draw'] = array('RunalyzePlot.flotHookColorPoints('
			. '[52.2, 50.7, 49.2, 47.7], '
			. '["'.self::COLOR_BAD.'", "'.self::COLOR_OKAY.'", "'.self::COLOR_GOOD.'", "'.self::COLOR_OKAY.'"], '
			. '"'.self::COLOR_BAD.'")'
		);
	}
}