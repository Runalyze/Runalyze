<?php
/**
 * This file contains class::Context
 * @package Runalyze\View\Activity
 */

namespace Runalyze\View\Activity;

use Request;
use Runalyze\Configuration;
use Runalyze\Model\Factory;
use Runalyze\Model\Activity;
use Runalyze\Model\Trackdata;

/**
 * Activity context
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity
 */
class Context {
	/**
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $Activity;

	/**
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata;

	/**
	 * @var \Runalyze\Model\Swimdata\Entity
	 */
	protected $Swimdata;

	/**
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Route;

	/**
	 * @var \Runalyze\Model\HRV\Entity
	 */
	protected $HRV;

	/**
	 * @var \Runalyze\Model\Sport\Entity
	 */
	protected $Sport;

	/**
	 * @var \Runalyze\View\Activity\Dataview
	 */
	protected $Dataview;

	/**
	 * Construct context
	 * @var int $activityID
	 * @var int $accountID
	 */
	public function __construct($activityID, $accountID) {
		$Factory = new Factory((int)$accountID);

		$this->Activity = $Factory->activity((int)$activityID);
		$this->Trackdata = $Factory->trackdata((int)$activityID);
		$this->Swimdata = $Factory->swimdata((int)$activityID);
		$this->Route = $this->Activity->get(Activity\Entity::ROUTEID) ? $Factory->route($this->Activity->get(Activity\Entity::ROUTEID)) : null;
		$this->HRV = $Factory->hrv((int)$activityID);
		$this->Sport = $Factory->sport($this->Activity->sportid());
		
		$this->Swimdata->fillDistanceArray($this->Trackdata);
		$this->Swimdata->fillSwolfArray($this->Trackdata);
		$this->Dataview = new Dataview($this->Activity);
	}

	/**
	 * Clone object
	 */
	public function __clone() {
		foreach ($this as $property => $value) {
			if (is_object($value)) {
				$this->{$property} = clone $value;
			}
		}
	}

	/**
	 * @return \Runalyze\Model\Activity\Entity
	 */
	public function activity() {
		return $this->Activity;
	}

	/**
	 * @return \Runalyze\Model\Trackdata\Entity
	 */
	public function trackdata() {
		return $this->Trackdata;
	}

	/**
	 * @return \Runalyze\Model\Swimdata\Entity
	 */
	public function swimdata() {
		return $this->Swimdata;
	}

	/**
	 * @return \Runalyze\Model\HRV\Entity
	 */
	public function hrv() {
		return $this->HRV;
	}

	/**
	 * @return \Runalyze\Model\Sport\Entity
	 */
	public function sport() {
		return $this->Sport;
	}

	/**
	 * @return \Runalyze\Model\Route\Entity
	 */
	public function route() {
		return $this->Route;
	}

	/**
	 * @return \Runalyze\View\Activity\Dataview
	 */
	public function dataview() {
		return $this->Dataview;
	}

	/**
	 * @return boolean
	 */
	public function hasTrackdata() {
		return !$this->Trackdata->isEmpty();
	}

	/**
	 * @return boolean
	 */
	public function hasRoute() {
		return !is_null($this->Route);
	}

	/**
	 * @return boolean
	 */
	public function hasSwimdata() {
		return !is_null($this->Swimdata);
	}

	/**
	 * @return boolean
	 */
	public function hasHRV() {
		return !$this->HRV->isEmpty();
	}


	/**
	 * @return boolean
	 */
	public function hideMap() {
		if (!Request::isOnSharedPage()) return false;

		$RoutePrivacy = Configuration::Privacy()->RoutePrivacy();
		if ($RoutePrivacy->showAlways()) return false;
		$type = $this->activity()->type();

		if ($RoutePrivacy->showRace()) {
			return (!$type->isCompetition());
		}

		return true;
	}

}
