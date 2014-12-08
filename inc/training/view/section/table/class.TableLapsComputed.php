<?php
/**
 * This file contains class::TableLapsComputed
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;

/**
 * Table: computed laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class TableLapsComputed extends TableLapsAbstract {
	/**
	 * Data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Set code
	 */
	protected function setCode() {
		$this->initData();
		$this->setDataToCode();
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$Rounds = $this->Training->GpsData()->getRoundsAsFilledArray();

		foreach ($Rounds as $Round) {
			$this->Data[] = array(
				'time'      => Duration::format($Round['time']),
				'distance'  => Distance::format($Round['distance']),
				'laptime'	=> Duration::format($Round['s']),
				'pace'      => SportFactory::getSpeedWithAppendixAndTooltip($Round['km'], $Round['s'], $this->Training->Sport()->id()),
				'heartrate' => Helper::Unknown($Round['heartrate']),
				'elevation' => Math::WithSign($Round['hm-up']).'/'.Math::WithSign(-$Round['hm-down']));
		}
	}

	/**
	 * Display data
	 */
	protected function setDataToCode() {
		$showCellForHeartrate = $this->Training->GpsData()->hasHeartrateData();
		$showCellForElevation = $this->Training->GpsData()->hasElevationData();

		$this->Code .= '<table class="fullwidth zebra-style">';
		$this->Code .= '<thead><tr>';
		$this->Code .= '<th>'.__('Distance').'</th>';
		$this->Code .= '<th>'.__('Time').'</th>';
		$this->Code .= '<th>'.__('Pace').'</th>';
		if ($showCellForHeartrate) $this->Code .= '<th>'.__('bpm').'</th>';
		if ($showCellForElevation) $this->Code .= '<th>'.__('elev.').'</th>';
		$this->Code .= '</tr></thead>';

		$this->Code .= '<tbody>';

		foreach ($this->Data as $Info) {
			$this->Code .= '<tr class="r">';
			$this->Code .= '<td>'.$Info['distance'].'</td>';
			$this->Code .= '<td>'.$Info['time'].'</td>';
			$this->Code .= '<td>'.$Info['pace'].'</td>';
			if ($showCellForHeartrate) $this->Code .= '<td>'.$Info['heartrate'].'</td>';
			if ($showCellForElevation) $this->Code .= '<td>'.$Info['elevation'].'</td>';
			$this->Code .= '</tr>';
		}

		$this->Code .= '</tbody>';
		$this->Code .= '</table>';
	}
}