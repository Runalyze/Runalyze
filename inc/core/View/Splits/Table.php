<?php
/**
 * This file contains class::Object
 * @package Runalyze\View
 */

namespace Runalyze\View\Splits;

use Runalyze\Model\Activity\Splits;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Pace;
use Runalyze\Parameter\Application\PaceUnit;

/**
 * Abstract view object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View
 */
class Table extends \Runalyze\View\Object {
	/**
	 * @var \Runalyze\Model\Activity\Splits\Entity
	 */
	protected $Splits;

	/**
	 * Halfs of a competition to display
	 * @var array
	 */
	protected $Halfs = array();

	/**
	 * Demanded pace
	 * @var \Runalyze\Activity\Pace
	 */
	protected $demandedPace;

	/**
	 * Achieved pace
	 * @var \Runalyze\Activity\Pace
	 */
	protected $achievedPace;

	/**
	 * Achieved pace (active rounds)
	 * @var \Runalyze\Activity\Pace
	 */
	protected $achievedPaceActive;

	/**
	 * @var string enum
	 */
	protected $PaceUnit;

	/**
	 * Code
	 * @var string
	 */
	protected $Code = '';

	/**
	 * @param \Runalyze\Model\Activity\Splits\Entity $splits
	 * @param string $paceUnit enum
	 */
	public function __construct(Splits\Entity $splits, $paceUnit = PaceUnit::MIN_PER_KM) {
		$this->Splits = $splits;
		$this->PaceUnit = $paceUnit;

		$this->init();
	}

	/**
	 * Code
	 * @return string
	 */
	public function code() {
		$this->setCode();

		return $this->Code;
	}

	/**
	 * Init
	 */
	private function init() {
		$this->achievedPace = new Pace($this->Splits->totalTime(), $this->Splits->totalDistance(), $this->PaceUnit);

		if ($this->Splits->hasActiveAndInactiveLaps()) {
			$timeActive = 0;
			$distanceActive = 0;

			foreach ($this->Splits->asArray() as $Split) {
				if ($Split->isActive()) {
					$timeActive += $Split->time();
					$distanceActive += $Split->distance();
				}
			}

			$this->achievedPaceActive = new Pace($timeActive, $distanceActive, $this->PaceUnit);
		} else {
			$this->achievedPaceActive = new Pace(0, 0, $this->PaceUnit);
		}

		$this->demandedPace = new Pace(0, 0, $this->PaceUnit);
	}

	/**
	 * Set demanded pace
	 * @param int $demandedPace
	 */
	public function setDemandedPace($demandedPace) {
		$this->demandedPace = new Pace(round($demandedPace), 1, $this->PaceUnit);
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
		$seperated = false;

		$showOnlyActive = $this->Splits->hasActiveAndInactiveLaps() && $this->Splits->hasActiveLaps(2);

		foreach ($this->Splits->asArray() as $Split) {
			$PaceObj = $Split->pace($this->PaceUnit);

			if ($showOnlyActive && !$Split->isActive()) {
				if (!$seperated && ($i > 0)) { // && ($i < $num_active)) {
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
			$this->Code .= '<td>'.Distance::format($Split->distance(), true, 2).'</td>';
			$this->Code .= '<td>'.Duration::format($Split->time()).'</td>';
			$this->Code .= '<td>'.$PaceObj->value().'<small>'.$PaceObj->appendix().'</small></td>';
			$this->Code .= '<td>'.$this->tdForPaceDifference($PaceObj).'</td>';
			$this->Code .= '</tr>';
		}

		$this->Code .= '</tbody><tbody class="top-spacer">';
	}

	/**
	 * Get td for pace difference
	 * @param \Runalyze\Activity\Pace $Pace
	 * @param bool $compareOnlyToDemanded [optional] default false
	 * @return string
	 */
	private function tdForPaceDifference(Pace $Pace, $compareOnlyToDemanded = false) {
		if (
			($compareOnlyToDemanded && ($this->demandedPace->isEmpty())) ||
			($this->achievedPaceActive->isEmpty()) && ($this->achievedPace->isEmpty())
		)
			return '';

		if (!$this->demandedPace->isEmpty()) {
			return $Pace->compareTo($this->demandedPace);
		} elseif (!$this->achievedPaceActive->isEmpty()) {
			return $Pace->compareTo($this->achievedPaceActive);
		}

		return $Pace->compareTo($this->achievedPace);
	}

	/**
	 * Set instruction
	 */
	private function setInstruction() {
		if ($this->demandedPace->isEmpty())
			return;

		$this->Code .= '
			<tr class="small r no-zebra">
				<td colspan="3">'.__('Goal').': </td>
					<td>'.$this->demandedPace->valueWithAppendix().'</td>
				<td></td>
			</tr>';
	}

	/**
	 * Set average
	 */
	private function setAverage() {
		if (!$this->achievedPaceActive->isEmpty()) {
			$this->Code .= '
				<tr class="small r no-zebra">
					<td colspan="3">'.__('Average (active)').': </td>
					<td>'.$this->achievedPaceActive->valueWithAppendix().'</td>
					<td>'.$this->tdForPaceDifference($this->achievedPaceActive, true).'</td>
				</tr>';
		}

		$this->Code .= '
			<tr class="small r no-zebra">
				<td colspan="3">'.__('Average (total)').': </td>
					<td>'.$this->achievedPace->valueWithAppendix().'</td>
				<td></td>
			</tr>';
	}

	/**
	 * Set halfs of competition
	 */
	private function setHalfsOfCompetitionToCode() {
		if (empty($this->Halfs)) {
			return;
		}

		$this->Code .= '<tr class="no-zebra"><td colspan="5">&nbsp;</td></tr>';
		$this->Code .= '<tr class="b bottom-spacer no-zebra"><td colspan="5">'.__('1st/2nd Half').'</td></tr>';

		$totalTime = 0;
		$totalDist = 0;

		foreach ($this->Halfs as $Half) {
			$totalTime += $Half['s'];
			$totalDist += $Half['km'];
		}

		$TotalPace = new Pace($totalTime, $totalDist, $this->PaceUnit);

		for ($i = 0, $num = count($this->Halfs); $i < $num; $i++) {
			$Pace = new Pace($this->Halfs[$i]['s'], $this->Halfs[$i]['km'], $this->PaceUnit);

			$this->Code .= '<tr class="r">
								<td></td>
								<td>'.Distance::format($this->Halfs[$i]['km'], true, 2).'</td>
								<td>'.Duration::format($this->Halfs[$i]['s']).'</td>
								<td>'.$Pace->valueWithAppendix().'</td>
								<td>'.$Pace->compareTo($TotalPace).'</td>
							</tr>';
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