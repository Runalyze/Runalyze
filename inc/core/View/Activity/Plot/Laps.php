<?php
/**
 * This file contains class::Laps
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\Activity\Pace as PaceObject;
use Runalyze\View\Activity;
use Helper;

/**
 * Plot for: Laps
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
abstract class Laps extends ActivityPlot {
	/**
	 * Demanded pace in s/km
	 * @var int
	 */
	protected $demandedPace = 0;

	/**
	 * Achieved pace in s/km
	 * @var int
	 */
	protected $achievedPace = 0;

	/**
	 * @var array
	 */
	protected $Labels = array();

	/**
	 * @var boolean
	 */
	protected $SplitsAreNotComplete;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * Unit
	 * @var \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	protected $PaceUnit;

	/**
	 * Load data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	abstract protected function loadData(Activity\Context $context);

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->PaceUnit = $context->sport()->paceUnit();

		$this->SplitsAreNotComplete = $this->splitsAreNotComplete($context);
		$this->loadData($context);

		if (!empty($this->Data) && $this->PaceUnit->isTimeFormat()) {
			$max = Helper::ceilFor(max($this->Data), 30000);

			$this->Plot->setYAxisTimeFormat('%M:%S');
		} else {
			$max = ceil(max($this->Data));

			$Pace = new PaceObject(0, 1);
			$Pace->setUnit($this->PaceUnit);
			$this->Plot->addYUnit(1, str_replace('&nbsp;', '', $Pace->appendix()), 1);
		}

		$this->Plot->setYLimits(1, 0, $max, false);
		$this->Plot->setXLabels($this->Labels);
		$this->Plot->showBars(true);

		$this->Plot->setYTicks(1, null);
		$this->Plot->Options['xaxis']['show'] = true; // force to show xaxis-labels, even if no time or distance array is given

		$this->addAnnotations();
	}

	/**
	 * Add annotations
	 */
	protected function addAnnotations() {
		if ($this->demandedPace > 0) {
			$demandedPace = $this->PaceUnit->rawValue($this->demandedPace);

			if ($this->PaceUnit->isTimeFormat()) {
				$demandedPace *= 1000;
			}

			$this->Plot->addThreshold("y", round($demandedPace), 'rgb(180,0,0)');
			//$this->Plot->addAnnotation(count($Data)-1, round($demandedPace), 'Soll: '.$this->PaceUnit->format($this->demandedPace), -10, -7);
		}

		if ($this->achievedPace > 0) {
			$achievedPace = $this->PaceUnit->rawValue($this->achievedPace);

			if ($this->PaceUnit->isTimeFormat()) {
				$achievedPace *= 1000;
			}

			$this->Plot->addThreshold("y", round($achievedPace), 'rgb(0,180,0)');
			$this->Plot->addAnnotation(0, round($achievedPace), '&oslash; '.$this->PaceUnit->format($this->achievedPace), -40, -7);
		}
	}

	/**
	 * Splits are not complete
	 * "Complete" means: all laps are active and fill total distance
	 *
	 * @param \Runalyze\View\Activity\Context $context
	 * @return boolean
	 */
	protected function splitsAreNotComplete(Activity\Context $context) {
		if ($context->activity()->splits()->isEmpty() || $context->activity()->splits()->totalDistance() <= 0) {
			return false;
		}

		if ($context->activity()->splits()->hasActiveAndInactiveLaps()) {
			return true;
		}

		if (abs($context->activity()->splits()->totalDistance() - $context->activity()->distance()) > 0.02 * $context->activity()->distance()) {
			return true;
		}

		return false;
	}
}
