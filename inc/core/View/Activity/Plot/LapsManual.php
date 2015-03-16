<?php
/**
 * This file contains class::LapsManual
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\Model\Activity\Splits;
use Runalyze\View\Activity;
use Runalyze\Activity\Duration;
use Runalyze\Util\StringReader;
use Runalyze\Activity\Pace as APace;

/**
 * Plot for: manual laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class LapsManual extends Laps {
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
	 * Set key and title for this plot
	 */
	protected function setKey() {
		$this->key = 'laps_manual';
		$this->title = __('Manual Laps');
	}

	/**
	 * Load data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function loadData(Activity\Context $context) {
		$Splits = $context->activity()->splits();

		if ($Splits->isEmpty() || $Splits->totalDistance() <= 0) {
			$this->Plot->raiseError( __('There are no manual laps.') );
			return;
		}

		$this->readDataFromSplits($Splits, !$Splits->hasActiveLaps(2));

		$num = count($this->Data);

		$Reader = new StringReader($context->activity()->comment());

		$this->demandedPace = $Reader->findDemandedPace();
		$this->achievedPace = array_sum($this->Data) / $num;

		$paceUnit = $context->sport()->paceUnit() == APace::NONE ? APace::STANDARD : $context->sport()->paceUnit();
		$this->manipulateData($num, $paceUnit);
	}

	/**
	 * @param \Runalyze\Model\Activity\Splits\Object $splits
	 * @param boolean $showInactive
	 */
	protected function readDataFromSplits(Splits\Object $splits, $showInactive) {
		foreach ($splits->asArray() as $split) {
			if (($showInactive || $split->isActive()) && $split->distance() > 0) {
				$this->Labels[] = $split->distance();
				$this->Data[] = $split->time() / $split->distance();
			}
		}
	}

	/**
	 * @param int $num
	 * @param enum $paceUnit
	 */
	protected function manipulateData($num, $paceUnit) {
		$paceInTime = ($paceUnit == APace::MIN_PER_KM || $paceUnit == APace::MIN_PER_100M);
		$pace = new APace(0, 1, $paceUnit);

		foreach ($this->Data as $key => $val) {
			if ($num > 35) {
				$this->Labels[$key] = array($key, round($this->Labels[$key], 1));
			} elseif ($num >= 20) {
				$this->Labels[$key] = array($key, number_format($this->Labels[$key], 1, '.', ''));
			} elseif ($num > 10) {
				$this->Labels[$key] = array($key, $this->Labels[$key].'k');
			} else {
				$this->Labels[$key] = array($key, $this->Labels[$key].' km');
			}

			$pace->setTime($val);

			if ($paceInTime) {
				$this->Data[$key] = 1000*$pace->secondsPerKm();
				if ($paceUnit == APace::MIN_PER_100M) {
					$this->Data[$key] /= 10;
				}
			} else {
				$this->Data[$key] = (float)str_replace(',', '.', $pace->value());
			}
		}

		$this->Plot->Data[] = array('label' => $this->title, 'data' => $this->Data);
	}

	/**
	 * Add annotations
	 */
	protected function addAnnotations() {
		if ($this->demandedPace > 0) {
			$this->Plot->addThreshold("y", $this->demandedPace*1000, 'rgb(180,0,0)');
			//$this->Plot->addAnnotation(count($Data)-1, $this->demandedPace*1000, 'Soll: '.Duration::format(round($this->demandedPace)), -10, -7);
		}

		if ($this->achievedPace > 0) {
			$this->Plot->addThreshold("y", $this->achievedPace*1000, 'rgb(0,180,0)');
			$this->Plot->addAnnotation(0, $this->achievedPace*1000, '&oslash; '.Duration::format(round($this->achievedPace)), -20, -7);
		}
	}
}
