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
	const COLOR = '#ffa500';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
     * @param int $sensorIndex
     */
	public function __construct(Activity\Context $context, $sensorIndex = 0) {
		$this->initOptions();
        if ($sensorIndex == 1) {
            $trackdataKey = Trackdata::THB_1;
            $this->Label = _('THb').' (2)';
        } else {
            $trackdataKey = Trackdata::THB_0;
            $this->Label = _('THb');
        }
		$this->initData($context->trackdata(), $trackdataKey);
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
