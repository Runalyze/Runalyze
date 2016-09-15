<?php
/**
 * This file contains class::Smo2_0
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

use \Plot;

/**
 * Plot for: smo2_1
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Plot\Series
 */
class Smo2_1 extends ActivitySeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(200,136,0)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::SMO2_1);
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Smo2 (2)');
		$this->Color = self::COLOR;

		$this->UnitString = '%';
		$this->UnitDecimals = 0;

		$this->TickSize = 10;
		$this->TickDecimals = 0;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}
}
