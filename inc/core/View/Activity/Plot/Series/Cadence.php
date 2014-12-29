<?php
/**
 * This file contains class::Cadence
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Object as Trackdata;
use Runalyze\View\Activity;
use Runalyze\Configuration;

use \Cadence as CadenceUnit;
use \CadenceRunning as CadenceUnitRunning;

/**
 * Plot for: Cadence
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class Cadence extends ActivitySeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(200,100,0)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$cadence = ($context->activity()->sportid() == Configuration::General()->runningSport())
				? new CadenceUnitRunning(0)
				: new CadenceUnit(0);

		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::CADENCE);
		$this->initStrings($cadence);
		$this->manipulateData($cadence);
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Color = self::COLOR;

		$this->UnitDecimals = 0;

		$this->TickSize = 10;
		$this->TickDecimals = 0;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

	/**
	 * Init strings
	 * @param \Cadence $cadence
	 */
	protected function initStrings(CadenceUnit $cadence) {
		$this->Label = $cadence->label();
		$this->UnitString = $cadence->unitAsString();
	}

	/**
	 * Manipulate data
	 * @param \Cadence $cadence
	 */
	protected function manipulateData(CadenceUnit $cadence) {
		$cadence->manipulateArray($this->Data);
	}
}