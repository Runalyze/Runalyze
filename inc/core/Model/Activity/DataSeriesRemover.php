<?php
/**
 * This file contains class::DataSeriesRemover
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use Runalyze\Model;
use Runalyze\Calculation\Route\Calculator;

/**
 * Remove single data series and update activity
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class DataSeriesRemover {
	/**
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $OldActivity;

	/**
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $Activity;

	/**
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $Trackdata = null;

	/**
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $OldTrackdata = null;

	/**
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $Route = null;

	/**
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $OldRoute = null;

	/**
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * @var int
	 */
	protected $AccountID;

	/**
	 * @var array
	 */
	protected $ActivityKeysForTrackdataKeys = [];

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param int $accountID
	 * @param \Runalyze\Model\Activity\Object $activity
	 * @param \Runalyze\Model\Factory $factory
	 */
	public function __construct(\PDO $connection, $accountID, Object $activity, Model\Factory $factory) {
		$this->PDO = $connection;
		$this->AccountID = $accountID;
		$this->Activity = $activity;
		$this->OldActivity = clone $activity;
		$this->Trackdata = $factory->trackdata($activity->id());
		$this->OldTrackdata = clone $this->Trackdata;
		$this->Route = $factory->route($activity->get(Object::ROUTEID));
		$this->OldRoute = clone $this->Route;

		$this->ActivityKeysForTrackdataKeys = [
			Model\Trackdata\Object::HEARTRATE => Model\Activity\Object::HR_AVG,
			Model\Trackdata\Object::CADENCE => Model\Activity\Object::CADENCE,
			Model\Trackdata\Object::VERTICAL_OSCILLATION => Model\Activity\Object::VERTICAL_OSCILLATION,
			Model\Trackdata\Object::GROUNDCONTACT => Model\Activity\Object::GROUNDCONTACT,
			Model\Trackdata\Object::POWER => Model\Activity\Object::POWER,
			Model\Trackdata\Object::TEMPERATURE => Model\Activity\Object::TEMPERATURE
		];
	}

	/**
	 * Remove single series from trackdata
	 * @param enum $key
	 */
	public function removeFromTrackdata($key) {
		if (isset($this->ActivityKeysForTrackdataKeys[$key])) {
			$this->removeFromActivityIfValueIsEqualToAverage($key, $this->ActivityKeysForTrackdataKeys[$key]);
		}

		$this->Trackdata->set($key, array());
	}

	/**
	 * @param enum $trackdataKey
	 * @param enum $activityKey
	 */
	protected function removeFromActivityIfValueIsEqualToAverage($trackdataKey, $activityKey) {
		// TODO: this does not use a filter for low values as ParserAbstractSingle does
		$dataArray = $this->Trackdata->get($trackdataKey);
		$dataAverage = round(array_sum($dataArray) / count($dataArray));

		if ($this->Activity->get($activityKey) == $dataAverage) {
			$this->Activity->set($activityKey, '');
		}
	}
	/**
	 * Remove single series from route
	 * @param enum $key
	 */
	public function removeFromRoute($key) {
		$this->Route->set($key, array());

		if ($key == Model\Route\Object::ELEVATIONS_ORIGINAL || $key == Model\Route\Object::ELEVATIONS_CORRECTED) {
			$Calculator = new Calculator($this->Route);
			$Calculator->calculateElevation();
		}
	}

	/**
	 * Remove gps path from route
	 */
	public function removeGPSpathFromRoute() {
		$this->Route->set(Model\Route\Object::LATITUDES, array());
		$this->Route->set(Model\Route\Object::LONGITUDES, array());
	}

	/**
	 * Save changes
	 */
	public function saveChanges() {
		$this->saveChangesForTrackdata();
		$this->saveChangesForRoute();

		$Updater = new Updater($this->PDO, $this->Activity, $this->OldActivity);
		$Updater->setAccountID($this->AccountID);
		$Updater->forceRecalculations();

		if (null !== $this->Trackdata) {
			$Updater->setTrackdata($this->Trackdata);
		}

		if (null !== $this->Route) {
			$Updater->setRoute($this->Route);
		}

		$Updater->update();
	}

	/**
	 * Save changes for trackdata
	 */
	protected function saveChangesForTrackdata() {
		if (null === $this->Trackdata || $this->OldTrackdata->isEmpty()) {
			return;
		}

		$this->Trackdata->synchronize();

		if ($this->Trackdata->isEmpty()) {
			$Deleter = new Model\Trackdata\Deleter($this->PDO, $this->Trackdata);
			$Deleter->setAccountID($this->AccountID);
			$Deleter->delete();

			$this->Trackdata = null;
		} else {
			$Updater = new Model\Trackdata\Updater($this->PDO, $this->Trackdata, $this->OldTrackdata);
			$Updater->setAccountID($this->AccountID);
			$Updater->update();
		}
	}

	/**
	 * Save changes for route
	 */
	protected function saveChangesForRoute() {
		if (null === $this->Route || $this->OldRoute->isEmpty()) {
			return;
		}

		$this->Route->synchronize();

		if ($this->Route->isEmpty()) {
			$Deleter = new Model\Route\Deleter($this->PDO, $this->Route);
			$Deleter->setAccountID($this->AccountID);
			$Deleter->delete();

			$this->Route = null;
			$this->Activity->set(Object::ROUTEID, 0);
			$this->Activity->set(Object::ELEVATION, 0);
		} else {
			$Updater = new Model\Route\Updater($this->PDO, $this->Route, $this->OldRoute);
			$Updater->setAccountID($this->AccountID);
			$Updater->update();
		}
	}
}