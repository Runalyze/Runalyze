<?php
/**
 * This file contains class::Thb
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

use \Plot;

/**
 * Plot for: thb
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Plot\Series
 */
class Thb extends ActivitySeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(0,0,255)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
     * @param int $sensorIndex
     */
	public function __construct(Activity\Context $context, $sensorIndex = 0) {
		$this->initOptions();
        if ($sensorIndex == 1) {
            $trackdataKey = Trackdata::THB_1;
            $this->Label = _('Thb (2)');
        } else {
            $trackdataKey = Trackdata::THB_0;
            $this->Label = _('Thb');
        }
		$this->initData($context->trackdata(), $trackdataKey);
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
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
