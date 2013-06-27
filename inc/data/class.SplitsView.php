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
			$this->achievedPace = $this->Splits->totalTime() / $this->Splits->totalDistance();

		if ($this->Splits->hasActiveAndInactiveLaps())
			$this->achievedPaceActive = array_sum($this->Splits->timesAsArray(false)) / array_sum($this->Splits->distancesAsArray(false));
	}

	/**
	 * Set demanded pace
	 * @param int $demandedPace
	 */
	public function setDemandedPace($demandedPace) {
		$this->demandedPace = $demandedPace;
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
		$this->displayTableHeader();
		$this->displaySplits();
		$this->displayInstruction();
		$this->displayAverage();
		$this->displayHalfsOfCompetition();
		$this->displayTableFooter();
	}

	/**
	 * Display table header
	 */
	private function displayTableHeader() {
		echo '<table class="small" cellspacing="0">
			<tr class="c b">
				<td>Distanz</td>
				<td>Zeit</td>
				<td>Pace</td>
				<td>Diff.</td>
			</tr>
			<tr class="space"><td colspan="4" /></tr>';
	}

	/**
	 * Display splits
	 */
	private function displaySplits() {
		foreach ($this->Splits->asArray() as $i => $Split) {
			$Time = Time::toSeconds($Split['time']);
			$Pace = $Split['km'] > 0 ? $Time / $Split['km'] : 0;
			$PaceDiff = ($this->demandedPace != 0) ? ($this->demandedPace - $Pace) : ($this->achievedPace - $Pace);
			$PaceClass = ($PaceDiff >= 0) ? 'plus' : 'minus';
			$PaceDiffString = ($PaceDiff >= 0) ? '+'.Time::toString($PaceDiff, false, 2) : '-'.Time::toString(-$PaceDiff, false, 2);

			echo '
			<tr class="a'.($i%2+2).' r '.($Split['active'] || !$this->Splits->hasActiveLaps() ? '' : 'unimportant').'">
				<td>'.Running::Km($Split['km'], 2).'</td>
				<td>'.$Split['time'].'</td>
				<td>'.SportSpeed::minPerKm($Split['km'], $Time).'/km</td>
				<td class="'.$PaceClass.'">'.$PaceDiffString.'/km</td>
			</tr>'.NL;
		}

		echo HTML::spaceTR(4);
	}

	/**
	 * Display instruction
	 */
	private function displayInstruction() {
		if ($this->demandedPace == 0)
			return;

		$AvgDiff = $this->achievedPaceActive > 0 ? ($this->demandedPace - $this->achievedPaceActive) : ($this->demandedPace - $this->achievedPace);
		$AvgClass = ($AvgDiff >= 0) ? 'plus' : 'minus';
		$AvgDiffString = ($AvgDiff >= 0) ? '+'.Time::toString($AvgDiff, false, 2) : '-'.Time::toString(-$AvgDiff, false, 2);

		echo '
			<tr class="r">
				<td colspan="2">Vorgabe: </td>
				<td>'.Time::toString($this->demandedPace).'/km</td>
				<td class="'.$AvgClass.'">'.$AvgDiffString.'/km</td>
			</tr>'.NL;
	}

	/**
	 * Display average
	 */
	private function displayAverage() {
		if ($this->achievedPaceActive > 0) {
			echo '
				<tr class="r">
					<td colspan="2">Schnitt (Aktiv): </td>
					<td>'.Time::toString($this->achievedPaceActive).'/km</td>
					<td></td>
				</tr>'.NL;
		}

		echo '
			<tr class="r">
				<td colspan="2">Schnitt: </td>
				<td>'.Time::toString($this->achievedPace).'/km</td>
				<td></td>
			</tr>'.NL;
	}

	/**
	 * Display halfs of competition
	 */
	private function displayHalfsOfCompetition() {
		if (empty($this->Halfs))
			return;

		echo HTML::emptyTR(4);
		echo '<tr class="b"><td colspan="4">1./2. Rennh&auml;lfte</td></tr>';
		echo HTML::spaceTR(4);

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

			echo '
			<tr class="a'.($i%2+2).' r">
				<td>'.Running::Km($this->Halfs[$i]['km'], 2).'</td>
				<td>'.Time::toString($this->Halfs[$i]['s']).'</td>
				<td>'.SportSpeed::minPerKm($this->Halfs[$i]['km'], $this->Halfs[$i]['s']).'/km</td>
				<td class="'.$PaceClass.'">'.$PaceDiffString.'/km</td>
			</tr>'.NL;
		}

		echo HTML::spaceTR(4);
	}

	/**
	 * Display table footer
	 */
	private function displayTableFooter() {
		echo '</table>';
	}
}