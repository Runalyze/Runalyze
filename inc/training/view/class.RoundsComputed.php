<?php
/**
 * This file contains class::RoundsComputed
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display computed rounds
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class RoundsComputed extends RoundsAbstract {
	/**
	 * Data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Get key
	 * @return string
	 */
	public function key() {
		return 'rounds-computed';
	}

	/**
	 * Get title
	 * @return string
	 */
	public function title() {
		return 'berechnete';
	}

	/**
	 * Display
	 */
	public function display() {
		$this->initData();
		$this->displayData();
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$Rounds = $this->Training->GpsData()->getRoundsAsFilledArray();

		foreach ($Rounds as $Round) {
			$this->Data[] = array(
				'time'      => Time::toString($Round['time']),
				'distance'  => Running::Km($Round['distance'], 2),
				'pace'      => SportFactory::getSpeedWithAppendixAndTooltip($Round['km'], $Round['s'], $this->Training->Sport()->id()),
				'heartrate' => Helper::Unknown($Round['heartrate']),
				'elevation' => Math::WithSign($Round['hm-up']).'/'.Math::WithSign(-$Round['hm-down']));
		}
	}

	/**
	 * Display data
	 */
	protected function displayData() {
		$showCellForHeartrate = $this->Training->GpsData()->hasHeartrateData();
		$showCellForElevation = $this->Training->GpsData()->hasElevationData();

		echo '<table class="small">';
		echo '<thead><tr>';
		echo '<th>Zeit</th>';
		echo '<th>Distanz</th>';
		echo '<th>Tempo</th>';
		if ($showCellForHeartrate) echo '<th>bpm</th>';
		if ($showCellForElevation) echo '<th>hm</th>';
		echo '</tr></thead>';

		echo '<tbody>';

		foreach ($this->Data as $i => $Info) {
			echo '<tr class="r '.HTML::trClass2($i).'">';
			echo '<td>'.$Info['time'].'</td>';
			echo '<td>'.$Info['distance'].'</td>';
			echo '<td>'.$Info['pace'].'</td>';
			if ($showCellForHeartrate) echo '<td>'.$Info['heartrate'].'</td>';
			if ($showCellForElevation) echo '<td>'.$Info['elevation'].'</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}
}