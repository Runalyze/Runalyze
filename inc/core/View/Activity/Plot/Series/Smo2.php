<?php
/**
 * This file contains class::Smo2
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

/**
 * Plot for: smo2
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Plot\Series
 */
class Smo2 extends ActivitySeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(0,204,0)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
     * @param int $sensorIndex
	 */
	public function __construct(Activity\Context $context, $sensorIndex = 0) {
		$this->initOptions();
        if ($sensorIndex == 1) {
            $trackdataKey = Trackdata::SMO2_1;
            $this->Label = __('Smo2 (2)');
        } else {
            $trackdataKey = Trackdata::SMO2_0;
            $this->Label = __('Smo2');
        }
		$this->initData($context->trackdata(), $trackdataKey);
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
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
