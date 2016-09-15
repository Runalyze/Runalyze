<?php
/**
 * This file contains class::Thb_0
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

use \Plot;

/**
 * Plot for: thb_0
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Plot\Series
 */
class Thb_0 extends ActivitySeries {
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
		$this->initData($context->trackdata(), Trackdata::THB_0);
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('THb');
		$this->Color = self::COLOR;

		$this->UnitString = 'g/dL';
		$this->UnitDecimals = 2;

		$this->TickSize = 1;
		$this->TickDecimals = 2;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}
}
