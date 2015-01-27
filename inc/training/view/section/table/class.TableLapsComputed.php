<?php
/**
 * This file contains class::TableLapsComputed
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;
use Runalyze\Model\Trackdata;
use Runalyze\Data\Laps\Laps;

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
		$this->constructLaps();
		$this->setDataToCode();

		// TODO: elevation data is disabled
		// - it is not in the trackdata
		// - calculator has to be used for partial array
	}

	/**
	 * Construct laps
	 */
	protected function constructLaps() {
		if ($this->Context->trackdata()->totalDistance() < 1) {
			$Distances = array(1);
		} else {
			$Distances = range(1, floor($this->Context->trackdata()->totalDistance()), 1);
		}

		$this->Laps = new Laps();
		$this->Laps->calculateFrom($Distances, $this->Context->trackdata(), $this->Context->route());
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$PaceUnit = $this->Context->sport()->paceUnit();
		$Loop = new Trackdata\Loop($this->Context->trackdata());

		do {
			$Loop->nextKilometer();

			$Pace = new Pace($Loop->difference(Trackdata\Object::TIME), $Loop->difference(Trackdata\Object::DISTANCE), $PaceUnit);

			$this->Data[] = array(
				'time'		=> Duration::format($Loop->time()),
				'distance'	=> Distance::format($Loop->distance()),
				'laptime'	=> Duration::format($Loop->difference(Trackdata\Object::TIME)),
				'pace'		=> $Pace->valueWithAppendix(),
				'heartrate' => Helper::Unknown(round($Loop->average(Trackdata\Object::HEARTRATE))),
				'elevation' => ''
			);
		} while (!$Loop->isAtEnd());
	}

	/**
	 * Display data
	 */
	protected function setDataToCode() {
		$showCellForHeartrate = $this->Context->trackdata()->has(Trackdata\Object::HEARTRATE);
		$showCellForElevation = $this->Context->hasRoute() && $this->Context->route()->hasElevations();

		$this->Code .= '<table class="fullwidth zebra-style">';
		$this->Code .= '<thead><tr>';
		$this->Code .= '<th>'.__('Distance').'</th>';
		$this->Code .= '<th>'.__('Time').'</th>';
		$this->Code .= '<th>'.__('Pace').'</th>';
		if ($showCellForHeartrate) $this->Code .= '<th>'.__('&oslash; bpm').'</th>';
		if ($showCellForElevation) $this->Code .= '<th>'.__('elev.').'</th>';
		$this->Code .= '</tr></thead>';

		$this->Code .= '<tbody>';

		//foreach ($this->Data as $Info) {
		foreach ($this->Laps->objects() as $Lap) {
			$this->Code .= '<tr class="r">';
			$this->Code .= '<td>'.$Lap->trackDistance()->string().'</td>';
			$this->Code .= '<td>'.$Lap->trackDuration()->string().'</td>';
			$this->Code .= '<td>'.$Lap->pace()->asMinPerKm().'<small>/km</small></td>';
			if ($showCellForHeartrate) $this->Code .= '<td>'.$Lap->HRavg()->inBPM().'<small>bpm</small></td>';
			if ($showCellForElevation) $this->Code .= '<td class="c">+'.$Lap->elevationUp().'/-'.$Lap->elevationDown().'</td>';
			$this->Code .= '</tr>';
		}

		$this->Code .= '</tbody>';
		$this->Code .= '</table>';
	}
}