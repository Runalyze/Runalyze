<?php
/**
 * This file contains class::LapsComputed
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\Activity\Pace as PaceObject;
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

		$RawData = $this->computeRounds($context);
		$num = count($RawData);
		$Pace = new PaceObject(0, 1);
		$Pace->setUnit($this->PaceUnit);

		foreach ($RawData as $key => $val) {
			$km = $key + 1;
			if ($num < 30) {
				$label = $km;
			} elseif ($num < 50) {
				$label = ($km%2 == 1 && $km > 0) ? $km : '';
			} elseif ($num < 100) {
				$label = ($km%5 == 0 && $km > 0) ? $km : '';
			} else {
				$label = ($km%10 == 0 && $km > 0) ? $km : '';
			}

			$this->Labels[$key] = array($key, $label);
			$Pace->setDistance($val['km'])->setTime($val['s']);

			if ($this->PaceUnit->isTimeFormat()) {
				$this->Data[$key] = 1000 * round($Pace->secondsPerKm() * $this->PaceUnit->factorForUnit());
			} else {
				$this->Data[$key] = (float)str_replace(',', '.', $Pace->value());
			}
		}

		$avgPace = new PaceObject($context->activity()->duration(), $context->activity()->distance());
		$this->achievedPace = $avgPace->secondsPerKm();
		$this->Plot->Data[] = array('label' => $this->title, 'data' => $this->Data);
	}

	/**
	 * @param \Runalyze\View\Activity\Context $context
	 * @return array
	 */
	protected function computeRounds(Activity\Context $context) {
		$Loop = new Trackdata\Loop($context->trackdata());
		$Rounds = array();

		do {
			$Loop->nextDistance();

			$Rounds[] = array(
				'km' => $Loop->difference(Trackdata\Entity::DISTANCE),
				's' => $Loop->difference(Trackdata\Entity::TIME)
			);
		} while (!$Loop->isAtEnd());

		return $Rounds;
	}
}
