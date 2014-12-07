<?php
/**
 * This file contains class::TableZonesAbstract
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Activity\Duration;

/**
 * Display zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TableZonesAbstract {
	/**
	 * Minimum distance to be shown as a zone
	 * @var double
	 */
	protected static $MINIMUM_DISTANCE_FOR_ZONE = 0.1;

	/**
	 * Training object
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Constructor
	 * @param TrainingObject $Training
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->initData();
		$this->convertData();
	}

	/**
	 * Get title for average
	 * @return string
	 */
	public function titleForAverage() { return ''; }

	/**
	 * Show average?
	 * @return bool
	 */
	private function showAverage() { return $this->titleForAverage() == ''; }

	/**
	 * Init data
	 */
	abstract protected function initData();

	/**
	 * Convert data
	 */
	private function convertData() {
		$totalTime = 0;
		$totalDist = 0;

		foreach ($this->Data as $Info) {
 			$totalTime += $Info['time'];
			$totalDist += $Info['distance'];
		}

		foreach ($this->Data as $i => $Info) {
			if ($totalTime > 0) {
				$percentage = round(100 * $Info['time'] / $totalTime, 1);
			} elseif ($totalDist > 0) {
				$percentage = round(100 * $Info['distance'] / $totalDist, 1);
			} else {
				$percentage = '-';
			}

			$this->Data[$i]['percentage'] = $percentage;
			$this->Data[$i]['time']       = $totalTime > 0 ? Duration::format($Info['time']) : '-';
			$this->Data[$i]['distance']   = Running::Km($Info['distance'], 2);
		}
	}

	/**
	 * Display
	 * @return string
	 */
	final public function getCode() {
		if (empty($this->Data))
			return;

		$Code = '<table class="fullwidth bar-chart-table">';
		$Code .= '<thead><tr>';
		$Code .= '<th>'.__('Zone').'</th>';
		$Code .= '<th></th>';
		$Code .= '<th>'.__('Time').'</th>';
		$Code .= '<th>'.__('Distance').'</th>';
		if ($this->showAverage()) $Code .= '<th>'.$this->titleForAverage().'</th>';
		$Code .= '</tr></thead>';

		$Code .= '<tbody>';
		$Code .= $this->getDataCode();
		$Code .= '</tbody>';
		$Code .= '</table>';

		return $Code;
	}

	/**
	* Display data
	 * @return string
	*/
	private function getDataCode() {
		$Code = '';

		foreach ($this->Data as $Info) {
			$Code .= '<tr>';
			$Code .= '<td class="bar-chart-label">'.$Info['zone'].'</td>';
			$Code .= '<td class="bar-chart-value-cell"><span class="bar-chart-value" style="width:'.$Info['percentage'].'%;"></span> <span class="bar-chart-text">'.$Info['percentage'].' &#37;</span></td>';
			$Code .= '<td>'.$Info['time'].'</td>';
			$Code .= '<td>'.$Info['distance'].'</td>';
			if ($this->showAverage()) $Code .= '<td>'.$Info['average'].'</td>';
			$Code .= '</tr>';
		}

		return $Code;
	}
}