<?php
/**
 * This file contains class::LapsComputed
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\Model\Trackdata;
use Runalyze\View\Activity;
use Runalyze\Activity\Pace as APace;

/**
 * Plot for: computed laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class LapsComputed extends Laps {
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
		$this->key = 'laps_computed';
		$this->title = __('Computed Laps');
	}

	/**
	 * Load data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function loadData(Activity\Context $context) {
		if (!$context->trackdata()->has(Trackdata\Object::DISTANCE) || !$context->trackdata()->has(Trackdata\Object::TIME)) {
			$this->Plot->raiseError( __('No GPS-data available. Can\\\'t compute laps.') );
			return;
		}

		$RawData = $this->computeRounds($context);
		$num = count($RawData);
		$paceUnit = $context->sport()->paceUnit() == APace::NONE ? APace::STANDARD : $context->sport()->paceUnit();
		$paceInTime = ($paceUnit == APace::MIN_PER_KM || $paceUnit == APace::MIN_PER_100M);
		$pace = new APace(0, 1, $paceUnit);

		foreach ($RawData as $key => $val) {
			$km = $key + 1;
			if ($num < 20) {
				$label = ($km%2 == 0 && $km > 0) ? $km.' km' : '';
			} elseif ($num < 50) {
				$label = ($km%5 == 0 && $km > 0) ? $km.' km' : '';
			} elseif ($num < 100) {
				$label = ($km%10 == 0 && $km > 0) ? $km.' km' : '';
			} else {
				$label = ($km%50 == 0 && $km > 0) ? $km.' km' : '';
			}

			$this->Labels[$key] = array($key, $label);
			$pace->setDistance($val['km'])->setTime($val['s']);

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
	 * @param \Runalyze\View\Activity\Context $context
	 * @return array
	 */
	protected function computeRounds(Activity\Context $context) {
		$Loop = new Trackdata\Loop($context->trackdata());
		$Rounds = array();

		do {
			$Loop->nextKilometer();

			$Rounds[] = array(
				'km' => $Loop->difference(Trackdata\Object::DISTANCE),
				's' => $Loop->difference(Trackdata\Object::TIME)
			);
		} while (!$Loop->isAtEnd());

		return $Rounds;
	}
}
