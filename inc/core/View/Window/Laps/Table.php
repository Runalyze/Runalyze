<?php
/**
 * This file contains class::Table
 * @package Runalyze\View\Window\Laps
 */

namespace Runalyze\View\Window\Laps;

use Runalyze\Activity\Duration;
use Runalyze\Activity\Elevation;
use Runalyze\Activity\Pace;
use Runalyze\Data\Laps\Laps;
use Runalyze\Model\Activity;
use Runalyze\View\Activity\Dataview;

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
	 * @var array
	 */
	protected $AdditionalKeys = array();

	/**
	 * @param \Runalyze\Data\Laps\Laps $laps
	 * @param \Runalyze\Activity\Duration $demandedTime
	 * @param \Runalyze\Activity\Pace $demandedPace
	 */
	public function __construct(Laps $laps, Duration $demandedTime, Pace $demandedPace) {
		$this->Laps = $laps;
		$this->DemandedTime = $demandedTime;
		$this->DemandedPace = $demandedPace;
		$this->AdditionalKeys = array_keys($this->Laps->at(0)->additionalValues());
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
						$this->tableHeaderForAdditionalKeys().
					'</tr>'.
				'</thead>';
	}

	/**
	 * @return string
	 */
	protected function tableHeaderForAdditionalKeys() {
		$Labels = new \DatasetLabels();
		$Code = '';

		foreach ($this->AdditionalKeys as $key) {
			$Code .= '<th class="small">'.$Labels->get($key).'</th>';
		}

		return $Code;
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
		$Lap = $this->Laps->at($i);
		$Lap->pace()->setUnit($this->DemandedPace->unit());

		return '<tr class="c '.($Lap->isActive() ? '' : 'unimportant').'">'.
				'<td class="small">'.($Lap->isActive() ? ($this->IndexActive++).'.' : '('.($this->IndexResting++).'.)').'</td>'.
				'<td>'.($Lap->hasTrackValues() ? $Lap->trackDistance()->string() : '-').'</td>'.
				'<td>'.($Lap->hasTrackValues() ? $Lap->trackDuration()->string() : '-').'</td>'.
				'<td>'.$Lap->distance()->string().'</td>'.
				'<td>'.$Lap->duration()->string().'</td>'.
				($this->DemandedTime->isZero() ? '' : '<td>'.$Lap->duration()->compareTo($this->DemandedTime, true).'</td>').
				'<td>'.$Lap->pace()->valueWithAppendix().'</td>'.
				($this->DemandedPace->isEmpty() ? '' : '<td>'.$Lap->pace()->compareTo($this->DemandedPace).'</td>').
				'<td>'.($Lap->hasHR() ? Helper::Unknown(round($Lap->HRavg()->inBPM()), '-') : '-').'</td>'.
				'<td>'.($Lap->hasHR() ? Helper::Unknown(round($Lap->HRmax()->inBPM()), '-') : '-').'</td>'.
				'<td>'.($Lap->hasElevation() ? '+'.Elevation::format($Lap->elevationUp(), false).'/-'.Elevation::format($Lap->elevationDown(), false) : '-').'</td>'.
				$this->additionalTableCellsFor($Lap).
			'</tr>';
	}

	/**
	 * @param \Runalyze\Data\Laps\Lap $Lap
	 * @return string
	 */
	protected function additionalTableCellsFor(\Runalyze\Data\Laps\Lap $Lap) {
		$Code = '';
		$View = new Dataview(new Activity\Object(
			$Lap->additionalValues()
		));

		foreach ($this->AdditionalKeys as $key) {
			switch ($key) {
				case Activity\Object::CADENCE:
					$Code .= '<td>'.$View->cadence()->asString().'</td>';
					break;

				case Activity\Object::GROUNDCONTACT:
					$Code .= '<td>'.$View->groundcontact().'</td>';
					break;

				case Activity\Object::VERTICAL_OSCILLATION:
					$Code .= '<td>'.$View->verticalOscillation().'</td>';
					break;

				case Activity\Object::STRIDE_LENGTH:
					$Code .= '<td>'.$View->strideLength()->string().'</td>';
					break;

				case Activity\Object::VDOT:
					$Code .= '<td>'.$View->vdot()->value().'</td>';
					break;

				default:
					$Code .= '<td></td>';
			}
		}

		return $Code;
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
		$Code .= '<td colspan="'.(3 + count($this->AdditionalKeys)).'"></td>';
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
