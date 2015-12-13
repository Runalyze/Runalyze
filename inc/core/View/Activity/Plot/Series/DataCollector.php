<?php
/**
 * This file contains class::DataCollector
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\Model\Trackdata\Loop;
use Runalyze\Configuration;

/**
 * Collect data from trackdata
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class DataCollector {
	/**
	 * @var int
	 */
	const X_AXIS_INDEX = 0;

	/**
	 * @var int
	 */
	const X_AXIS_TIME = 1;

	/**
	 * @var int
	 */
	const X_AXIS_DISTANCE = 2;

	/**
	 * @var string
	 */
	protected $Key;

	/**
	 * @var \Runalyze\Model\Trackdata\Loop;
	 */
	protected $Loop;

	/**
	 * Step distance
	 * @var int [m]
	 */
	protected $StepDistance;

	/**
	 * @var int
	 */
	protected $XAxis = 0;

	/**
	 * @var array
	 */
	protected $Data = array();

	/**
	 * @var \Runalyze\Parameter\Application\ActivityPlotPrecision
	 */
	protected $Precision;

	/**
	 * @var boolean
	 */
	protected $KnowsDistance;

	/**
	 * Construct collector
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param int $key
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Trackdata $trackdata, $key) {
		if (!$trackdata->has($key)) {
			throw new \InvalidArgumentException('Trackdata has no data for "'.$key.'".');
		}

		$this->Key = $key;
		$this->Precision = Configuration::ActivityView()->plotPrecision();
		$this->KnowsDistance = $trackdata->has(Trackdata::DISTANCE);

		$this->init($trackdata);
		$this->collect();
	}

	/**
	 * Data
	 * @return array
	 */
	public function data() {
		return $this->Data;
	}

	/**
	 * Type of x-axis
	 * @return int
	 */
	public function xAxis() {
		return $this->XAxis;
	}

	/**
	 * Init loop
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 */
	protected function init(Trackdata $trackdata) {
		$this->Loop = new Loop($trackdata);

		$this->defineStepSize($trackdata);
		$this->defineXAxis($trackdata);
	}

	/**
	 * Collect data
	 */
	protected function collect() {
		do {
			$this->move();

			$value = $this->Loop->average($this->Key);

			if ($this->XAxis == self::X_AXIS_DISTANCE) {
				$this->Data[(string)$this->Loop->current(Trackdata::DISTANCE)] = $value;
			} elseif ($this->XAxis == self::X_AXIS_TIME) {
				$this->Data[(string)$this->Loop->current(Trackdata::TIME).'000'] = $value;
			} else {
				$this->Data[] = $value;
			}
		} while (!$this->Loop->isAtEnd());
	}

	/**
	 * Get next step for plot data
	 * @return bool 
	 */
	protected function move() {
		if ($this->KnowsDistance && $this->Precision->byDistance()) {
			$this->Loop->moveDistance( $this->StepDistance );
		} else {
			$this->Loop->nextStep();
		}
	}

	/**
	 * Set step size
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 */
	protected function defineStepSize(Trackdata $trackdata) {
		if ($this->Precision->byPoints() && $trackdata->num() > $this->Precision->numberOfPoints()) {
			$this->Loop->setStepSize( round($trackdata->num() / $this->Precision->numberOfPoints ()) );
		} elseif ($this->Precision->byDistance()) {
			$this->StepDistance = $this->Precision->distanceStep() / 1000;
		}
	}

	/**
	 * Define x-axis
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 */
	protected function defineXAxis(Trackdata $trackdata) {
		if (Configuration::ActivityView()->usesTimeAsXAxis() && $trackdata->has(Trackdata::TIME) && $trackdata->totalTime() > 0) {
			$this->XAxis = self::X_AXIS_TIME;
		} elseif ($trackdata->has(Trackdata::DISTANCE) && $trackdata->totalDistance() > 0) {
			$this->XAxis = self::X_AXIS_DISTANCE;
		} elseif ($trackdata->has(Trackdata::TIME) && $trackdata->totalTime() > 0) {
			$this->XAxis = self::X_AXIS_TIME;
		} else {
			$this->XAxis = self::X_AXIS_INDEX;
		}
	}
}