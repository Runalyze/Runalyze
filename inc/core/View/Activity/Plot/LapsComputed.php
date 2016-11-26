<?php
/**
 * This file contains class::LapsComputed
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\Activity\Pace as PaceObject;
use Runalyze\Configuration;
use Runalyze\Model\Trackdata;
use Runalyze\View\Activity;

/**
 * Plot for: computed laps
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class LapsComputed extends Laps {
	/**
	 * Set key and title for this plot
	 */
	protected function setKey() {
		$this->key = 'laps_computed';
		$this->title = __('Computed Laps');
	}

	/**
	 * Load data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function loadData(Activity\Context $context) {
		if (!$context->trackdata()->has(Trackdata\Entity::DISTANCE) || !$context->trackdata()->has(Trackdata\Entity::TIME)) {
			$this->Plot->raiseError( __('No GPS-data available. Can\\\'t compute laps.') );
			return;
		}

		$laps = $this->computeRounds($context);
		$num = $laps->num();
		$Pace = new PaceObject(0, 1);
		$Pace->setUnit($this->PaceUnit);

		foreach ($laps->objects() as $i => $lap) {
			$km = $i + 1;
			if ($num < 30) {
				$label = $km;
			} elseif ($num < 50) {
				$label = ($km%2 == 1 && $km > 0) ? $km : '';
			} elseif ($num < 100) {
				$label = ($km%5 == 0 && $km > 0) ? $km : '';
			} else {
				$label = ($km%10 == 0 && $km > 0) ? $km : '';
			}

			$this->Labels[$i] = array($i, $label);
			$Pace->setDistance($lap->distance()->kilometer())->setTime($lap->duration()->seconds());

			if ($this->PaceUnit->isTimeFormat()) {
				$this->Data[$i] = 1000 * round($Pace->secondsPerKm() * $this->PaceUnit->factorForUnit());
			} else {
				$this->Data[$i] = (float)str_replace(',', '.', $Pace->value());
			}
		}

		$avgPace = new PaceObject($context->activity()->duration(), $context->activity()->distance());
		$this->achievedPace = $avgPace->secondsPerKm();
		$this->Plot->Data[] = array('label' => $this->title, 'data' => $this->Data);
	}

	/**
	 * @param Activity\Context $context
	 * @return \Runalyze\Data\Laps\Laps
	 */
	protected function computeRounds(Activity\Context $context) {
		$singleDistance = Configuration::General()->distanceUnitSystem()->distanceToKmFactor();
		$totalDistance = $context->trackdata()->totalDistance();

		if ($totalDistance < 2*$singleDistance) {
			$Distances = array($singleDistance);
		} else {
			$Distances = range($singleDistance, $totalDistance, $singleDistance);

			if (false === $Distances) {
				$Distances = array($singleDistance);
			}
		}

		$laps = new \Runalyze\Data\Laps\Laps();
		$laps->calculateFrom($Distances, $context->trackdata());

		return $laps;
	}
}
