<?php
/**
 * This file contains class::TrainingPlotLapsComputed
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for computed laps
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotLapsComputed extends TrainingPlotLaps {
	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'laps_computed';
		$this->title = __('Computed laps');
	}

	/**
	 * Init data
	 */
	protected function initData() {
		if (!$this->Training->hasArrayDistance() || !$this->Training->hasArrayTime()) {
			$this->Plot->raiseError( __('No GPS-data available. Cannot compute laps.') );
			return;
		}

		$RawData = $this->Training->GpsData()->getRoundsAsFilledArray();
		$num     = count($RawData);

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
			$this->Data[$key]   = $val['km'] > 0 ? $val['s']*1000/$val['km'] : 0;
		}

		$this->Plot->Data[] = array('label' => $this->title, 'data' => $this->Data);
	}
}