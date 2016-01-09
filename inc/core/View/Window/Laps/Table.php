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
	 * @param bool $isRunning
	 */
	public function __construct(Laps $laps, Duration $demandedTime, Pace $demandedPace, $isRunning)
	{
		$this->Laps = $laps;
		$this->DemandedTime = $demandedTime;
		$this->DemandedPace = $demandedPace;

		$this->defineAdditionalKeys($isRunning);
	}

	/**
	 * @param $isRunning
	 */
	protected function defineAdditionalKeys($isRunning) {
		$this->AdditionalKeys = array_keys($this->Laps->at(0)->additionalValues());

		if (!$isRunning) {
			$this->AdditionalKeys = array_diff($this->AdditionalKeys, array(
				Activity\Entity::GROUNDCONTACT,
				Activity\Entity::GROUNDCONTACT_BALANCE,
				Activity\Entity::VERTICAL_OSCILLATION,
				Activity\Entity::VERTICAL_RATIO,
				Activity\Entity::STRIDE_LENGTH,
				Activity\Entity::VDOT
			));
		}
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
						'<th>&oslash; '.__('HR').'</th>'.
						'<th>'.__('max.').' '.__('HR').'</th>'.
						'<th class="{sorter: false}">'.__('elevation').'</th>'.
						$this->tableHeaderForAdditionalKeys().
					'</tr>'.
				'</thead>';
	}

	/**
	 * @return string
	 */
	protected function tableHeaderForAdditionalKeys() {
		$Code = '';

		foreach ($this->AdditionalKeys as $key) {
			$Code .= '<th class="small">'.$this->labelForAdditionalValue($key).'</th>';
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
				'<td>'.($Lap->hasHR() ? Helper::Unknown($Lap->HRavg()->string(), '-') : '-').'</td>'.
				'<td>'.($Lap->hasHR() ? Helper::Unknown($Lap->HRmax()->string(), '-') : '-').'</td>'.
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
		$View = new Dataview(new Activity\Entity(
			$Lap->additionalValues()
		));

		foreach ($this->AdditionalKeys as $key) {
			switch ($key) {
				case Activity\Entity::CADENCE:
					$Code .= '<td>'.$View->cadence()->asString().'</td>';
					break;

				case Activity\Entity::GROUNDCONTACT:
					$Code .= '<td>'.$View->groundcontact().'</td>';
					break;

				case Activity\Entity::GROUNDCONTACT_BALANCE:
					$Code .= '<td>'.$View->groundcontactBalance().'</td>';
					break;

				case Activity\Entity::VERTICAL_OSCILLATION:
					$Code .= '<td>'.$View->verticalOscillation().'</td>';
					break;
				    
				case Activity\Entity::VERTICAL_RATIO:
					$Code .= '<td>'.$View->verticalRatio().'</td>';
					break;

				case Activity\Entity::STRIDE_LENGTH:
					$Code .= '<td>'.$View->strideLength()->string().'</td>';
					break;

				case Activity\Entity::VDOT:
					$Code .= '<td>'.$View->vdot()->value().'</td>';
					break;

				case Activity\Entity::POWER:
					$Code .= '<td>'.$View->power().'</td>';
					break;

				default:
					$Code .= '<td></td>';
			}
		}

		return $Code;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function labelForAdditionalValue($key) {
		switch ($key) {
			case Activity\Entity::CADENCE:
				return __('Cadence');
			case Activity\Entity::GROUNDCONTACT:
				return __('Ground contact time');
			case Activity\Entity::GROUNDCONTACT_BALANCE:
				return __('Ground contact balance');
			case Activity\Entity::VERTICAL_OSCILLATION:
				return __('Vertical oscillation');
			case Activity\Entity::VERTICAL_RATIO:
				return __('Vertical ratio');
			case Activity\Entity::STRIDE_LENGTH:
				return __('Stride length');
			case Activity\Entity::VDOT:
				return __('VDOT');
			case Activity\Entity::POWER:
				return __('Power');
		}

		return '';
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
			return '';
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
