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
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $Activity;

	/**
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $Trackdata;
        
	/**
	 * @var \Runalyze\Model\Swimdata\Object
	 */
	protected $Swimdata;

	/**
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $Route;

	/**
	 * @var \Runalyze\Model\HRV\Object
	 */
	protected $HRV;

	/**
	 * @var \Runalyze\Model\Sport\Object
	 */
	protected $Sport;

	/**
	 * @var \Runalyze\View\Activity\Dataview
	 */
	protected $Dataview;

	/**
	 * Construct context
	 * @var int $activityID
	 * @var in $accountID
	 */
	public function __construct($activityID, $accountID) {
		$Factory = new Factory((int)$accountID);

		$this->Activity = $Factory->activity((int)$activityID);
		$this->Trackdata = $Factory->trackdata((int)$activityID);
		$this->Swimdata = $Factory->swimdata((int)$activityID);
		$this->Route = $this->Activity->get(Activity\Object::ROUTEID) ? $Factory->route($this->Activity->get(Activity\Object::ROUTEID)) : null;
		$this->HRV = $Factory->hrv((int)$activityID);
		$this->Sport = $Factory->sport($this->Activity->sportid());
		
		$this->Swimdata->fillDistanceArray($this->Trackdata);
		$this->Swimdata->fillSwolfArray($this->Trackdata);
		$this->Dataview = new Dataview($this->Activity);

	}

	/**
	 * @return \Runalyze\Model\Activity\Object
	 */
	public function activity() {
		return $this->Activity;
	}

	/**
	 * @return \Runalyze\Model\Trackdata\Object
	 */
	public function trackdata() {
		return $this->Trackdata;
	}
        
	/**
	 * @return \Runalyze\Model\Swimdata\Object
	 */
	public function swimdata() {
		return $this->Swimdata;
	}

	/**
	 * @return \Runalyze\Model\HRV\Object
	 */
	public function hrv() {
		return $this->HRV;
	}

	/**
	 * @return \Runalyze\Model\Sport\Object
	 */
	public function sport() {
		return $this->Sport;
	}

	/**
	 * @return \Runalyze\Model\Route\Object
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
