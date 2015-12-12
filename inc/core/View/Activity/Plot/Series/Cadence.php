<?php
/**
 * This file contains class::Cadence
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Data;
use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;
use Runalyze\Configuration;

use \Plot;

/**
 * Plot for: Cadence
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class Cadence extends ActivityPointSeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(41,128,185)';

	/**
	 * @var boolean
	 */
	protected $isRunning = false;

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->isRunning = ($context->activity()->sportid() == Configuration::General()->runningSport());
		$cadence = $this->isRunning ? new Data\Cadence\Running() : new Data\Cadence\General(0);

		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::CADENCE);
		$this->initStrings($cadence);
		$this->manipulateData($cadence);
		$this->setManualAverage($cadence->useFactor($context->activity()->cadence()));
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
	 * @param \Runalyze\Data\Cadence\AbstractCadence $cadence
	 */
	protected function initStrings(Data\Cadence\AbstractCadence $cadence) {
		$this->Label = $cadence->label();
		$this->UnitString = $cadence->unitAsString();
	}

	/**
	 * Manipulate data
	 * @param \Runalyze\Data\Cadence\AbstractCadence $cadence
	 */
	protected function manipulateData(Data\Cadence\AbstractCadence $cadence) {
		$cadence->manipulateArray($this->Data);
	}

	/**
	 * Add to plot
	 * @param \Plot $Plot
	 * @param int $yAxis
	 * @param boolean $addAnnotations [optional]
	 */
	public function addTo(Plot &$Plot, $yAxis, $addAnnotations = true) {
		parent::addTo($Plot, $yAxis, $addAnnotations);

		if ($this->isRunning) {
			$this->setColorThresholdsAbove($Plot, 185, 173, 162, 151);
		}
	}
}