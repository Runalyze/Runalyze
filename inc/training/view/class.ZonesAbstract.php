<?php
/**
 * This file contains class::ZonesAbstract
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
abstract class ZonesAbstract {
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
	 * Get title
	 * @return string
	 */
	abstract public function title();

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

		foreach ($this->Data as $Info)
			$totalTime += $Info['time'];

		foreach ($this->Data as $i => $Info) {
			$this->Data[$i]['percentage'] = round(100 * $Info['time'] / $totalTime, 1);
			$this->Data[$i]['time']       = Time::toString($Info['time'], false, $Info['time'] < 60 ? 2 : false);
			$this->Data[$i]['distance']   = Running::Km($Info['distance'], 2);
		}
	}

	/**
	 * Display
	 */
	final public function display() {
		if (empty($this->Data))
			return;

		echo '<div class="dataBox training-zones left">';
		echo '<div class="databox-header">'.$this->title().'</div>';

		echo '<table class="small" style="white-space:nowrap;">';
		echo '<thead><tr>';
		echo '<th>Zone</th>';
		echo '<th>Anteil</th>';
		echo '<th>Zeit</th>';
		echo '<th>Distanz</th>';
		if ($this->showAverage()) echo '<th>'.$this->titleForAverage().'</th>';
		echo '</tr></thead>';

		echo '<tbody>';
		$this->displayData();
		echo '</tbody>';
		echo '</table>';

		echo '</div>';
	}

	/**
	* Display data
	*/
	private function displayData() {
		foreach ($this->Data as $i => $Info) {
			$opacity = 0.5 + $Info['percentage']/200;

			echo '<tr class="r '.HTML::trClass2($i).'" style="opacity:'.$opacity.';">';
			echo '<td>'.$Info['zone'].'</td>';
			echo '<td>'.$Info['percentage'].'&nbsp;&#37;</td>';
			echo '<td>'.$Info['time'].'</td>';
			echo '<td>'.$Info['distance'].'</td>';
			if ($this->showAverage()) echo '<td>'.$Info['average'].'</td>';
			echo '</tr>';
		}
	}
}