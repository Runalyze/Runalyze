<?php
/**
 * This file contains class::DataCollector
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Object as Trackdata;
use Runalyze\Model\Trackdata\Loop;
use Runalyze\Configuration;
use Runalyze\Parameter\Application\ActivityPlotPrecision;

/**
 * Collect data from trackdata
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class DataCollector {
	/**
	 * @var enum
	 */
	const X_AXIS_INDEX = 0;

	/**
	 * @var enum
	 */
	const X_AXIS_TIME = 1;

	/**
	 * @var enum
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
	 * @var enum
	 */
	protected $XAxis = 0;

	/**
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Construct collector
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 * @param enum $key
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Trackdata $trackdata, $key) {
		if (!$trackdata->has($key)) {
			throw new \InvalidArgumentException('Trackdata has no data for "'.$key.'".');
		}

		$this->Key = $key;

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
	 * @return enum
	 */
	public function xAxis() {
		return $this->XAxis;
	}

	/**
	 * Init loop
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 */
	protected function init(Trackdata $trackdata) {
		$this->Loop = new Loop($trackdata);

		$this->defineStepSize($trackdata, Configuration::ActivityView()->plotPrecision());
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
		if (Configuration::ActivityView()->plotPrecision()->byDistance()) {
			$this->Loop->moveDistance( $this->StepDistance );
		} else {
			$this->Loop->nextStep();
		}
	}

	/**
	 * Set step size
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 * @param \Runalyze\Parameter\Application\ActivityPlotPrecision $precision
	 */
	protected function defineStepSize(Trackdata $trackdata, ActivityPlotPrecision $precision) {
		if ($precision->byPoints() && $trackdata->num() > $precision->numberOfPoints()) {
			$this->Loop->setStepSize( round($trackdata->num() / $precision->numberOfPoints ()) );
		} elseif ($precision->byDistance()) {
			$this->StepDistance = $precision->distanceStep() * 1000;
		}
	}

	/**
	 * Define x-axis
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 */
	protected function defineXAxis(Trackdata $trackdata) {
		if ($trackdata->has(Trackdata::DISTANCE) && $trackdata->totalDistance() > 0) {
			$this->XAxis = self::X_AXIS_DISTANCE;
		} elseif ($trackdata->has(Trackdata::TIME) && $trackdata->totalTime() > 0) {
			$this->XAxis = self::X_AXIS_TIME;
		} else {
			$this->XAxis = self::X_AXIS_INDEX;
		}
	}
}