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
		return 'Kilometerzeiten';
	}

	/**
	 * Display
	 */
	public function display() {
		$this->initData();
		$this->displayData();
		$this->displayLinkToInfoWindow();
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
				'laptime'	=> Time::toString($Round['s']),
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

		echo '<table class="fullwidth zebra-style zebra-blue small">';
		echo '<thead><tr>';
		echo '<th>Zeit</th>';
		echo '<th>Distanz</th>';
		echo '<th>Tempo</th>';
		if ($showCellForHeartrate) echo '<th>bpm</th>';
		if ($showCellForElevation) echo '<th>hm</th>';
		echo '</tr></thead>';

		echo '<tbody>';

		foreach ($this->Data as $i => $Info) {
			echo '<tr class="r">';
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

	/**
	 * Display link to info window
	 */
	protected function displayLinkToInfoWindow() {
		echo '<p class="c">'.Ajax::window('<a href="'.$this->Training->Linker()->urlToRoundsInfo().'">&raquo; genaue Auswertung &ouml;ffnen</a>', 'normal').'</p>';
	}
}