<?php
/**
 * This file contains class::VerticalOscillation
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Object as Trackdata;
use Runalyze\View\Activity;

/**
 * Plot for: Vertical oscillation
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class VerticalOscillation extends ActivitySeries {
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
		$this->initData($context->trackdata(), Trackdata::VERTICAL_OSCILLATION);
		$this->manipulateData();
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Vertical oscillation');
		$this->Color = self::COLOR;

		$this->UnitString = 'cm';
		$this->UnitDecimals = 1;

		$this->TickSize = 1;
		$this->TickDecimals = 1;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

	/**
	 * Manipulate data
	 */
	protected function manipulateData() {
		$this->Data = array_map(array($this, 'correctUnit'), $this->Data);
	}

	/**
	 * Change value by internal factor
	 * @param int $value
	 * @return float
	 */
	protected function correctUnit($value) {
		return 0.1*$value;
	}

	/**
	 * Average
	 * @param int $decimals [optional]
	 * @return int
	 */
	protected function avg($decimals = 1) {
		return parent::avg($decimals);
	}
}