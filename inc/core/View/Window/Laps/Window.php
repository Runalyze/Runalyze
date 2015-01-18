<?php
/**
 * This file contains class::Window
 * @package Runalyze\View\Window\Laps
 */

namespace Runalyze\View\Window\Laps;

use Runalyze\View\Activity\Context;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;
use Runalyze\Data\Laps\Laps;
use Runalyze\Data\Laps\Lap;

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
	const HANDMADE_LIMIT = 0.2;

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
		$this->DemandedPace->setUnit(Pace::MIN_PER_KM);

		if ($this->Context->activity()->splits()->isEmpty()) {
			$_POST['distance'] = $this->LapDistance;
		}
	}

	/**
	 * Read request
	 */
	protected function readPropertiesFromRequest() {
		if ((float)Request::param('distance') > 0) {
			$this->LapDistance = min($this->Context->trackdata()->totalDistance(), (float)Request::param('distance'));
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
			$this->ManualDistances = explode(',', Request::param('manual-distances'));
		}
	}

	/**
	 * @return boolean
	 */
	protected function handmadeLapsDiffer() {
		if ($this->Context->activity()->splits()->isEmpty()) {
			return false;
		}

		return (abs($this->Context->trackdata()->totalDistance() - $this->Context->activity()->splits()->totalDistance()) > self::HANDMADE_LIMIT);
	}

	/**
	 * Calculate values
	 */
	protected function constructLaps() {
		$this->Laps = new Laps();

		if (empty($this->ManualDistances) && $this->LapDistance == 0 && !$this->Context->activity()->splits()->isEmpty()) {
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
		}

		$Distances = array();
		$sum = 0;

		foreach ($this->Context->activity()->splits()->asArray() as $split) {
			$Distances[] = $split->distance() + $sum;
			$sum += $split->distance();
		}

		$this->Laps->calculateFrom($Distances, $this->Context->trackdata(), $this->Context->route());
		$num = $this->Laps->num();

		foreach ($this->Context->activity()->splits()->asArray() as $i => $split) {
			if ($i < $num) {
				$this->Laps->at($i)->setMode( $split->isActive() ? Lap::MODE_ACTIVE : Lap::MODE_RESTING );
			}
		}
	}

	/**
	 * Construct laps from trackdata
	 */
	protected function constructLapsFromTrackdata() {
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
		$Table = new Table(
			$this->Laps,
			$this->DemandedTime,
			$this->DemandedPace
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