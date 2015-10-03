<?php
/**
 * This file contains class::Table
 * @package Runalyze\View\Window\Laps
 */

namespace Runalyze\View\Window\Laps;

use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;
use Runalyze\Data\Laps\Laps;

use Ajax;
use Helper;
use FormularFieldset;

/**
 * Display table for laps
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Window\Laps
 */
class Table {
	/**
	 * @var string
	 */
	const CSS_ID = 'rounds-info-window';

	/**
	 * @var \Runalyze\Data\Laps\Laps
	 */
	protected $Laps;

	/**
	 * Demanded time
	 * @var \Runalyze\Activity\Duration
	 */
	protected $DemandedTime;

	/**
	 * Demanded pace
	 * @var \Runalyze\Activity\Pace
	 */
	protected $DemandedPace;

	/**
	 * @var \Runalyze\Activity\Pace
	 */
	protected $AveragePace = null;

	/**
	 * @var \Runalyze\Activity\Duration
	 */
	protected $AverageTime = null;

	/**
	 * @var int
	 */
	protected $IndexActive = 1;

	/**
	 * @var int
	 */
	protected $IndexResting = 1;

	/**
	 * @param \Runalyze\Data\Laps\Laps $laps
	 * @param \Runalyze\Activity\Duration $demandedTime
	 * @param \Runalyze\Activity\Pace $demandedPace
	 */
	public function __construct(Laps $laps, Duration $demandedTime, Pace $demandedPace) {
		$this->Laps = $laps;
		$this->DemandedTime = $demandedTime;
		$this->DemandedPace = $demandedPace;
	}

	/**
	 * @param \Runalyze\Activity\Pace $pace
	 * @param \Runalyze\Activity\Duration $time
	 */
	public function setAverage(Pace $pace, Duration $time = null) {
		$this->AveragePace = $pace;
		$this->AverageTime = $time;
	}

	/**
	 * Display rounds
	 */
	public function display() {
		$Fieldset = new FormularFieldset( __('Laps') );
		$Fieldset->setId('rounds');
		$Fieldset->setHtmlCode( $this->tableHeader().$this->tableBody().$this->tableFooter() );
		$Fieldset->display();

		Ajax::createTablesorterFor('#'.self::CSS_ID, true);
	}

	/**
	 * @return string
	 */
	protected function tableHeader() {
		return '<table class="fullwidth zebra-style zebra-blue" id="'.self::CSS_ID.'">'.
				'<thead>'.
					'<tr>'.
						'<th class="{sorter: \'order\'}">#</th>'.
						'<th class="{sorter: \'distance\'}">'.__('Distance').'</th>'.
						'<th class="{sorter: \'resulttime\'}">'.__('Time').'</th>'.
						'<th class="{sorter: \'distance\'}">'.__('Lap').'</th>'.
						'<th class="{sorter: \'resulttime\'}">'.__('Duration').'</th>'.
						($this->DemandedTime->isZero() ? '' : '<th>'.__('Diff.').'</th>').
						'<th>'.__('Pace').'</th>'.
						'<th>'.__('Diff.').'</th>'.
						'<th>'.__('&oslash; bpm').'</th>'.
						'<th>'.__('max. bpm').'</th>'.
						'<th class="{sorter: false}">'.__('elevation').'</th>'.
					'</tr>'.
				'</thead>';
	}

	/**
	 * @return string
	 */
	protected function tableBody() {
		$Code = '<tbody class="top-and-bottom-border">';

		for ($i = 0, $num = $this->Laps->num(); $i < $num; ++$i) {
			$Code .= $this->tableRowFor($i);
		}

		$Code .= '</tbody>';

		return $Code;
	}

	/**
	 * @param int $i
	 * @return string
	 */
	protected function tableRowFor($i) {
		$this->Laps->at($i)->pace()->setUnit($this->DemandedPace->unit());

		return '<tr class="c '.($this->Laps->at($i)->isActive() ? '' : 'unimportant').'">'.
				'<td class="small">'.($this->Laps->at($i)->isActive() ? ($this->IndexActive++).'.' : '('.($this->IndexResting++).'.)').'</td>'.
				'<td>'.($this->Laps->at($i)->hasTrackValues() ? $this->Laps->at($i)->trackDistance()->string() : '-').'</td>'.
				'<td>'.($this->Laps->at($i)->hasTrackValues() ? $this->Laps->at($i)->trackDuration()->string() : '-').'</td>'.
				'<td>'.$this->Laps->at($i)->distance()->string().'</td>'.
				'<td>'.$this->Laps->at($i)->duration()->string().'</td>'.
				($this->DemandedTime->isZero() ? '' : '<td>'.$this->Laps->at($i)->duration()->compareTo($this->DemandedTime, true).'</td>').
				'<td>'.$this->Laps->at($i)->pace()->valueWithAppendix().'</td>'.
				($this->DemandedPace->isEmpty() ? '' : '<td>'.$this->Laps->at($i)->pace()->compareTo($this->DemandedPace).'</td>').
				'<td>'.($this->Laps->at($i)->hasHR() ? Helper::Unknown(round($this->Laps->at($i)->HRavg()->inBPM()), '-') : '-').'</td>'.
				'<td>'.($this->Laps->at($i)->hasHR() ? Helper::Unknown(round($this->Laps->at($i)->HRmax()->inBPM()), '-') : '-').'</td>'.
				'<td>'.($this->Laps->at($i)->hasElevation() ? '+'.$this->Laps->at($i)->elevationUp().'/-'.$this->Laps->at($i)->elevationDown() : '-').'</td>'.
				'</tr>';
	}

	/**
	 * @return string
	 */
	protected function tableFooter() {
		$Code  = '<tbody>';
		$Code .= '<tr class="no-zebra"><td colspan="4" class="r">'.__('Average').':</td>';
		$Code .= '<td class="c">'.($this->AverageTime != null ? $this->AverageTime->string() : '').'</td>';
		$Code .= ($this->DemandedTime->isZero() ? '' : '<td></td>');
		$Code .= '<td class="c">'.($this->AveragePace != null ? $this->AveragePace->valueWithAppendix() : '').'</td>';
		$Code .= ($this->DemandedPace->isEmpty() ? '' : '<td></td>');
		$Code .= '<td colspan="3"></td>';
		$Code .= '</tr>';
		$Code .= '</tbody>';
		$Code .= '</table>';
		$Code .= $this->checkboxToToggleInactiveSplits();

		return $Code;
	}

	/**
	 * @return string
	 */
	protected function checkboxToToggleInactiveSplits() {
		if ($this->IndexActive == 1 || $this->IndexResting == 1) {
			return;
		}

		$Code  = '<p class="checkbox-first">';
		$Code .= '<label>';
		$Code .= '<input type="checkbox" name="toggle-active-splits" id="toggle-active-splits" checked> ';
		$Code .= __('Show inactive splits');
		$Code .= '</label>';
		$Code .= '</p>';

		$Code .= Ajax::wrapJS(
			'$("#toggle-active-splits").click(function(){'.
				'$("#'.self::CSS_ID.' tr.unimportant").toggle();'.
			'});'
		);

		return $Code;
	}
}
