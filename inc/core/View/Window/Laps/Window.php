<?php
/**
 * This file contains class::Window
 * @package Runalyze\View\Window\Laps
 */

namespace Runalyze\View\Window\Laps;

use Runalyze\Configuration;
use Runalyze\Data\Laps\Calculator;
use Runalyze\View\Activity\Context;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;
use Runalyze\Data\Laps\Laps;
use Runalyze\Data\Laps\Lap;
use Runalyze\Parameter\Application\PaceUnit;

use Request;

/**
 * Window for detailed laps info
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Window\Laps
 */
class Window {
	/**
	 * @var float
	 */
	const HANDMADE_LIMIT_TIME = 10;

	/**
	 * @var float
	 */
	const HANDMADE_LIMIT_DISTANCE = 0.2;

	/**
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context;

	/**
	 * @var \Runalyze\Data\Laps\Laps
	 */
	protected $Laps;

	/**
	 * Lap distance
	 * @var mixed
	 */
	protected $LapDistance = 0;

	/**
	 * Lap time
	 * @var mixed
	 */
	protected $LapTime = 0;

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
	 * Manual distances
	 * @var array
	 */
	protected $ManualDistances = array();

	/**
	 * Manual times
	 * @var array
	 */
	protected $ManualTimes = array();

	/**
	 * @param \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Context $context) {
		$this->Context = $context;

		$this->prepareProperties();
		$this->readPropertiesFromRequest();
		$this->constructLaps();
	}

	/**
	 * Prepare internal properties
	 */
	protected function prepareProperties() {
		$this->DemandedTime = new Duration();
		$this->DemandedPace = new Pace(0, 1);
		$this->DemandedPace->setUnitEnum(PaceUnit::MIN_PER_KM);

		if (isset($_POST['distance'])) {
			$_POST['distance'] = str_replace(',', '.', $_POST['distance']);
		} elseif ($this->Context->activity()->splits()->isEmpty()) {
			$_POST['distance'] = Configuration::General()->distanceUnitSystem()->distanceToKmFactor();
		}
	}

	/**
	 * Read request
	 */
	protected function readPropertiesFromRequest() {
		if ((float)Request::param('distance') > 0) {
			$this->LapDistance = min($this->Context->trackdata()->totalDistance(), (float)Request::param('distance'));
		}

		if (strlen(Request::param('time')) > 0) {
			$Time = new Duration(Request::param('time'));
			$this->LapTime = min($this->Context->trackdata()->totalTime(), $Time->seconds());
		}

		if (strlen(Request::param('demanded-time')) > 0) {
			$this->DemandedTime->fromString(Request::param('demanded-time'));

			if ($this->LapDistance > 0) {
				$this->DemandedPace->setTime( $this->DemandedTime->seconds() / $this->LapDistance );
			}
		} elseif ($this->LapDistance > 0) {
			$this->DemandedTime->fromSeconds( $this->Context->trackdata()->totalTime() / $this->Context->trackdata()->totalDistance() * $this->LapDistance );
			$this->DemandedPace->setTime( $this->DemandedTime->seconds() / $this->LapDistance );
		}

		if (strlen(Request::param('demanded-pace')) > 0) {
			$this->DemandedPace->fromMinPerKm(Request::param('demanded-pace'));
			$this->DemandedTime->fromSeconds( $this->LapDistance * $this->DemandedPace->secondsPerKm() );
		} elseif ($this->DemandedPace->secondsPerKm() == 0) {
			$this->DemandedPace = $this->Context->dataview()->pace();
		}

		if (strlen(Request::param('manual-distances')) > 0) {
			$this->ManualDistances = Calculator::getDistancesFromString(Request::param('manual-distances'));
		}

		if (strlen(Request::param('manual-times')) > 0) {
			$this->ManualTimes = Calculator::getTimesFromString(Request::param('manual-times'));
		}
	}

	/**
	 * @return bool
	 */
	protected function handmadeLapsDiffer() {
		if ($this->Context->activity()->splits()->isEmpty()) {
			return false;
		}

		return ($this->handmadeLapsTimeDiffers() && $this->handmadeLapsDistanceDiffers());
	}

	/**
	 * @return bool
	 */
	protected function handmadeLapsTimeDiffers() {
		return (abs($this->Context->trackdata()->totalTime() - $this->Context->activity()->splits()->totalTime()) > self::HANDMADE_LIMIT_TIME);
	}

	/**
	 * @return bool
	 */
	protected function handmadeLapsDistanceDiffers() {
		return (abs($this->Context->trackdata()->totalDistance() - $this->Context->activity()->splits()->totalDistance()) > self::HANDMADE_LIMIT_DISTANCE);
	}

	/**
	 * Calculate values
	 */
	protected function constructLaps() {
		$this->Laps = new Laps();
		$this->Laps->enableCalculationOfAdditionalValues();

		if (
			empty($this->ManualDistances) && $this->LapDistance == 0 &&
			empty($this->ManualTimes) && $this->LapTime == 0 &&
			!$this->Context->activity()->splits()->isEmpty()
		) {
			$this->constructLapsFromSplits();
		} else {
			$this->constructLapsFromTrackdata();
		}
	}

	/**
	 * Construct laps from splits object
	 */
	protected function constructLapsFromSplits() {
		if ($this->handmadeLapsDiffer() && !Request::param('calculate-for-splits')) {
			$this->Laps->readFrom($this->Context->activity()->splits());
			return;
		}

		if ($this->handmadeLapsTimeDiffers()) {
			$this->constructLapsFromSplitsDistances();
		} else {
			$this->constructLapsFromSplitsTimes();
		}

		$num = $this->Laps->num();

		foreach ($this->Context->activity()->splits()->asArray() as $i => $split) {
			if ($i < $num) {
				$this->Laps->at($i)->setMode( $split->isActive() ? Lap::MODE_ACTIVE : Lap::MODE_RESTING );
			}
		}
	}

	/**
	 * Construct laps from splits object by distances
	 */
	protected function constructLapsFromSplitsDistances() {
		$Distances = array();
		$sum = 0;

		foreach ($this->Context->activity()->splits()->asArray() as $split) {
			$Distances[] = $split->distance() + $sum;
			$sum += $split->distance();
		}

		$this->Laps->calculateFrom($Distances, $this->Context->trackdata(), $this->Context->route());
	}

	/**
	 * Construct laps from splits object by times
	 */
	protected function constructLapsFromSplitsTimes() {
		$Times = array();
		$sum = 0;

		foreach ($this->Context->activity()->splits()->asArray() as $split) {
			$Times[] = $split->time() + $sum;
			$sum += $split->time();
		}

		$this->Laps->calculateFromTimes($Times, $this->Context->trackdata(), $this->Context->route());
	}

	/**
	 * Construct laps from trackdata
	 */
	protected function constructLapsFromTrackdata() {
		if (!empty($this->ManualTimes) || $this->LapTime != 0) {
			$this->constructLapsFromTrackdataByTimes();
		} else {
			$this->constructLapsFromTrackdataByDistances();
		}
	}

	/**
	 * Construct laps from trackdata by times
	 */
	protected function constructLapsFromTrackdataByTimes() {
		$Times = $this->ManualTimes;

		if (empty($Times)) {
			if ($this->LapTime == 0) {
				$this->LapTime = 300;
			}

			$LastStep = $this->LapTime * floor($this->Context->trackdata()->totalTime() / $this->LapTime);

			if ($LastStep <= $this->LapTime) {
				$Times = array($this->LapTime);
			} else {
				$Times = range($this->LapTime, $LastStep, $this->LapTime);
			}
		}

		$this->Laps->calculateFromTimes($Times, $this->Context->trackdata(), $this->Context->route());
	}

	/**
	 * Construct laps from trackdata by distances
	 */
	protected function constructLapsFromTrackdataByDistances() {
		$Distances = $this->ManualDistances;

		if (empty($Distances)) {
			if ($this->LapDistance == 0) {
				$this->LapDistance = 1;
			}

			$LastStep = $this->LapDistance * floor($this->Context->trackdata()->totalDistance() / $this->LapDistance);

			if ($LastStep <= $this->LapDistance) {
				$Distances = array($this->LapDistance);
			} else {
				$Distances = range($this->LapDistance, $LastStep, $this->LapDistance);
			}
		}

		$this->Laps->calculateFrom($Distances, $this->Context->trackdata(), $this->Context->route());
	}

	/**
	 * Display
	 */
	public function display() {
		echo '<div class="panel-heading">';
		echo '<h1>'.$this->Context->dataview()->titleWithComment().'</h1>';
		echo '</div>';

		echo '<div class="panel-content">';
		$this->displayForm();
		$this->displayTable();
		echo '</div>';
	}

	/**
	 * Display formular
	 */
	protected function displayForm() {
		$Form = new Form();

		if (!$this->Context->activity()->splits()->isEmpty()) {
			if ($this->handmadeLapsDiffer()) {
				$Form->activateHandmadeDifference();
			}

			$Form->activateHandmadeInfo();
		}

		$Form->display();
	}

	/**
	 * Display rounds
	 */
	protected function displayTable() {
		$this->DemandedPace->setUnit($this->Context->sport()->paceUnit());

		$Table = new Table(
			$this->Laps,
			$this->DemandedTime,
			$this->DemandedPace,
			($this->Context->sport()->id() == Configuration::General()->runningSport())
		);

		if ($this->LapDistance > 0) {
			$Table->setAverage(
				$this->Context->dataview()->pace(),
				new Duration($this->Context->trackdata()->totalTime() / $this->Context->trackdata()->totalDistance() * $this->LapDistance)
			);
		} else {
			$Table->setAverage($this->Context->dataview()->pace());
		}

		$Table->display();
	}
}
