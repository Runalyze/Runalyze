<?php
/**
 * This file contains class::SplitsView
 * @package Runalyze\Data\Splits
 */
/**
 * Class for displaying splits
 * @author Hannes Christiansen
 * @package Runalyze\Data\Splits
 */
class SplitsView {
	/**
	 * Splits
	 * @var Splits
	 */
	protected $Splits = null;

	/**
	 * Halfs of a competition to display
	 * @var array
	 */
	protected $Halfs = array();

	/**
	 * Demanded pace
	 * @var int
	 */
	protected $demandedPace = 0;

	/**
	 * Achieved pace
	 * @var int
	 */
	protected $achievedPace = 0;

	/**
	 * Achieved pace (active rounds)
	 * @var int
	 */
	protected $achievedPaceActive = 0;

	/**
	 * Code
	 * @var string
	 */
	protected $Code = '';

	/**
	 * Constructor
	 * @param Splits $Splits
	 */
	public function __construct(Splits &$Splits) {
		$this->Splits = $Splits;

		$this->init();
	}

	/**
	 * Init
	 */
	private function init() {
		if ($this->Splits->totalDistance() > 0)
			$this->achievedPace = round($this->Splits->totalTime() / $this->Splits->totalDistance());

		if ($this->Splits->hasActiveAndInactiveLaps())
			$this->achievedPaceActive = round( array_sum($this->Splits->timesAsArray(false)) / array_sum($this->Splits->distancesAsArray(false)) );
	}

	/**
	 * Set demanded pace
	 * @param int $demandedPace
	 */
	public function setDemandedPace($demandedPace) {
		$this->demandedPace = round($demandedPace);
	}

	/**
	 * Set halfs of competition
	 * @param array $Halfs
	 */
	public function setHalfsOfCompetition(array $Halfs) {
		$this->Halfs = $Halfs;
	}

	/**
	 * Display
	 */
	public function display() {
		$this->setCode();

		echo $this->Code;
	}

	/**
	 * Get code
	 * @return string
	 */
	public function getCode() {
		$this->setCode();

		return $this->Code;
	}

	/**
	 * Display
	 */
	public function setCode() {
		$this->Code = '';

		$this->setTableHeader();
		$this->setSplits();
		$this->setInstruction();
		$this->setAverage();
		$this->setHalfsOfCompetitionToCode();
		$this->setTableFooter();
	}

	/**
	 * Set table header
	 */
	private function setTableHeader() {
		$this->Code .= '<table class="fullwidth zebra-style"><tbody>';

		$this->Code .= '<thead>';
		$this->Code .= '<tr class="r">';
		$this->Code .= '<th></th>';
		$this->Code .= '<th>'.__('Distance').'</th>';
		$this->Code .= '<th>'.__('Time').'</th>';
		$this->Code .= '<th>'.__('Pace').'</th>';
		$this->Code .= '<th>'.__('diff').'</th>';
		$this->Code .= '</tr>';
		$this->Code .= '</thead>';
	}

	/**
	 * Set splits
	 */
	private function setSplits() {
		$i = 0;
		$num_active = count( $this->Splits->distancesAsArray(false) );
		$seperated = false;

		foreach ($this->Splits->asArray() as $Split) {
			$Time = Time::toSeconds($Split['time']);
			$Pace = $Split['km'] > 0 ? $Time / $Split['km'] : 0;

			if ($this->Splits->hasActiveAndInactiveLaps() && !$Split['active']) {
				if (!$seperated && ($i > 0) && ($i < $num_active)) {
					$this->Code .= '<tr class="space-line"><td colspan="5"></td></tr>';
					$this->Code .= '<tr class="space-line zebra-corrector"><td colspan="5"></td></tr>';
					$seperated = true;
				}

				continue;
			} else {
				$seperated = false;
			}

			$this->Code .= '<tr class="r">';
			$this->Code .= '<td class="c">'.(++$i).'.</td>';
			$this->Code .= '<td>'.Running::KmFormat($Split['km'], 2).' <small>km</small></td>';
			$this->Code .= '<td>'.$Split['time'].'</td>';
			$this->Code .= '<td>'.SportSpeed::minPerKm($Split['km'], $Time).'<small>/km</small></td>';
			$this->Code .= $this->tdForPaceDifference($Pace);
			$this->Code .= '</tr>';
		}

		$this->Code .= '</tbody><tbody class="top-spacer">';
	}

	/**
	 * Get td for pace difference
	 * @param int $Pace
	 * @param bool $compareOnlyToDemanded [optional] default false
	 * @return string
	 */
	private function tdForPaceDifference($Pace, $compareOnlyToDemanded = false) {
		if (($compareOnlyToDemanded && ($this->demandedPace == 0)) || ($this->achievedPaceActive == 0) && ($this->achievedPace == 0))
			return '<td></td>';

		$PaceDiff = ($this->demandedPace != 0) ? ($this->demandedPace - $Pace) : ($this->achievedPaceActive > 0 ? ($this->achievedPaceActive - $Pace) : ($this->achievedPace - $Pace));
		$PaceClass = ($PaceDiff >= 0) ? 'plus' : 'minus';
		$PaceDiffString = ($PaceDiff >= 0) ? '+'.Time::toString($PaceDiff, false, 2) : '-'.Time::toString(-$PaceDiff, false, 2);

		return '<td class="'.$PaceClass.'">'.$PaceDiffString.'/km</td>';
	}

	/**
	 * Set instruction
	 */
	private function setInstruction() {
		if ($this->demandedPace == 0)
			return;

		$this->Code .= '
			<tr class="small r no-zebra">
				<td colspan="3">'.__('Goal').': </td>
				<td>'.Time::toString($this->demandedPace).'/km</td>
				<td></td>
			</tr>'.NL;
	}

	/**
	 * Set average
	 */
	private function setAverage() {
		if ($this->achievedPaceActive > 0) {
			$this->Code .= '
				<tr class="small r no-zebra">
					<td colspan="3">'.__('Average (active)').': </td>
					<td>'.Time::toString($this->achievedPaceActive).'/km</td>
					'.$this->tdForPaceDifference($this->achievedPaceActive, true).'
				</tr>'.NL;
		}

		$this->Code .= '
			<tr class="small r no-zebra">
				<td colspan="3">'.__('Average (total)').': </td>
				<td>'.Time::toString($this->achievedPace).'/km</td>
				<td></td>
			</tr>'.NL;
	}

	/**
	 * Set halfs of competition
	 */
	private function setHalfsOfCompetitionToCode() {
		if (empty($this->Halfs))
			return;

		$this->Code .= '<tr class="no-zebra"><td colspan="5">&nbsp;</td></tr>';
		$this->Code .= '<tr class="b bottom-spacer no-zebra"><td colspan="5">'.__('1st/2nd Half').'</td></tr>';

		$totalTime = 0;
		$totalDist = 0;

		foreach ($this->Halfs as $Half) {
			$totalTime += $Half['s'];
			$totalDist += $Half['km'];
		}

		$TotalPace = $totalTime / $totalDist;

		for ($i = 0, $num = count($this->Halfs); $i < $num; $i++) {
			$Pace           = $this->Halfs[$i]['s'] / $this->Halfs[$i]['km'];
			$PaceDiff       = $TotalPace - $Pace;
			$PaceClass      = ($PaceDiff >= 0) ? 'plus' : 'minus';
			$PaceDiffString = ($PaceDiff >= 0) ? '+'.Time::toString($PaceDiff, false, 2) : '-'.Time::toString(-$PaceDiff, false, 2);

			$this->Code .= '
			<tr class="r">
				<td></td>
				<td>'.Running::Km($this->Halfs[$i]['km'], 2).'</td>
				<td>'.Time::toString($this->Halfs[$i]['s']).'</td>
				<td>'.SportSpeed::minPerKm($this->Halfs[$i]['km'], $this->Halfs[$i]['s']).'/km</td>
				<td class="'.$PaceClass.'">'.$PaceDiffString.'/km</td>
			</tr>'.NL;
		}
	}

	/**
	 * set table footer
	 */
	private function setTableFooter() {
		$this->Code .= '</tbody>';
		$this->Code .= '</table>';
	}
}