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
		$this->Trackdata = $factory->trackdata($activity->id());
		$this->OldTrackdata = clone $this->Trackdata;
		$this->Route = $factory->route($activity->get(Object::ROUTEID));
		$this->OldRoute = clone $this->Route;
	}

	/**
	 * Remove single series from trackdata
	 * @param enum $key
	 */
	public function removeFromTrackdata($key) {
		$this->Trackdata->set($key, array());
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
		$oldObject = clone $this->Activity;

		$this->saveChangesForTrackdata();
		$this->saveChangesForRoute();

		$Updater = new Updater($this->PDO, $this->Activity, $oldObject);
		$Updater->setAccountID($this->AccountID);
		$Updater->forceRecalculations();

		if (NULL !== $this->Trackdata) {
			$Updater->setTrackdata($this->Trackdata);
		}

		if (NULL !== $this->Route) {
			$Updater->setRoute($this->Route);
		}

		$Updater->update();
	}

	/**
	 * Save changes for trackdata
	 */
	protected function saveChangesForTrackdata() {
		if (NULL === $this->Trackdata) {
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
		if (NULL === $this->Route) {
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