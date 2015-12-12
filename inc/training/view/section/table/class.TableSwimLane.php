<?php
/**
 * This file contains class::TableLapsComputed
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Model\Trackdata;
use Runalyze\Model\Swimdata;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Data\Stroketype;

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
		$TrackLoop = new Trackdata\Loop($this->Context->trackdata());
		$Stroketype = new Stroketype(Stroketype::FREESTYLE);
		$Distance = new Distance(0);

		$max = $Loop->num();

		for ($i = 1; $i <= $max; ++$i) {
			$Stroketype->set($Loop->stroketype());
			$Distance->set($TrackLoop->distance());

			$this->Code .= '<tr class="r">';
			$this->Code .= '<td>'.$i.'.</td>';
			$this->Code .= '<td>'.$Distance->stringMeter().'</td>';
			$this->Code .= '<td>'.Duration::format($TrackLoop->difference(Trackdata\Entity::TIME)).'</td>';
			$this->Code .= '<td>'.$Loop->swolf().'</td>';
			$this->Code .= '<td>'.$Loop->stroke().'</td>';
			$this->Code .= '<td>'.$Stroketype->shortstring().'</td>';
			$this->Code .= '</tr>';

			$TrackLoop->nextStep();
			$Loop->nextStep();
		}

		$this->Code .= '</tbody>';
		$this->Code .= '</table>';
	}
}