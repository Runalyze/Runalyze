<?php
/**
 * This file contains class::TableLapsComputed
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Model\Trackdata;
use Runalyze\Model\Swimdata;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;

/**
 * Display swim lanes
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\DataObjects\Training\View\Section
 */

class TableSwimLane extends TableLapsAbstract {
	/**
	 * Data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Set code
	 */
	protected function setCode() {
		$this->setDataToCode();
	}

	/**
	 * Display data
	 */
	protected function setDataToCode() {

		$this->Code .= '<table class="fullwidth zebra-style">';
		$this->Code .= '<thead><tr>';
                $this->Code .= '<th></th>';
                $this->Code .= '<th>'.__('Distance').'</th>';
		$this->Code .= '<th>'.__('Time').'</th>';
		$this->Code .= '<th>'.__('Swolf').'</th>';
                $this->Code .= '<th>'.__('Strokes').'</th>';
                $this->Code .= '<th>'.__('Type').'</th>';
		$this->Code .= '</tr></thead>';

		$this->Code .= '<tbody>';
                $Loop = new Swimdata\Loop($this->Context->swimdata());
                $Time = new Trackdata\Loop($this->Context->trackdata());
      
                $lasttime = 0;
                $i = 1;
                while ($Loop->nextStep()) {
                        
                    $duration = $Time->current('time') - $lasttime;
                    $duration = new Duration($duration);
                    $distance = new Distance($Time->current('distance'));
                    $this->Code .= '<tr class="r">';
                    $this->Code .= '<td>'.$i.'.</td>';
                    $this->Code .= '<td>'.$distance->string(false, 3).'</td>';
                    $this->Code .= '<td>'.$duration->string().'</td>';
                    $this->Code .= '<td>'.($duration + $Loop->stroke()).'</td>';
                    $this->Code .= '<td>'.$Loop->stroke().'</td>';
                    $this->Code .= '<td>'.$Loop->stroketype().'</td>';
                    $this->Code .= '</tr>';      
                    $lasttime = $Time->current('time');
                    $Time->move('time', 1);
                    $i++;
                }


		$this->Code .= '</tbody>';
		$this->Code .= '</table>';
	}
}