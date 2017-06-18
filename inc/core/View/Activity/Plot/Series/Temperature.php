<?php
/**
 * This file contains class::Temperature
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Configuration;
use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

/**
 * Plot for: Temperature
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class Temperature extends ActivitySeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(100,0,200)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::TEMPERATURE);
		$this->manipulateData();
	}


	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Temperature');
		$this->Color = self::COLOR;

		$this->UnitString = Configuration::General()->temperatureUnit()->unit();
		$this->UnitDecimals = 1;

		$this->TickSize = 10;
		$this->TickDecimals = 0;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

    protected function manipulateData() {
        $temperatureUnit = Configuration::General()->temperatureUnit();

        if ($temperatureUnit->isFahrenheit()) {
            $this->Data = array_map(function($value) {
                return round($value * 1.8 + 32.0, 1);
            }, $this->Data);
        }
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
