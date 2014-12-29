<?php
/**
 * This file contains class::Context
 * @package Runalyze\View\Activity
 */

namespace Runalyze\View\Activity;

use Runalyze\Model\Factory;
use Runalyze\Model\Activity;

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
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $Route;

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
		$Factory = new Factory($accountID);

		$this->Activity = $Factory->activity($activityID);
		$this->Trackdata = $Factory->trackdata($activityID);
		$this->Route = $this->Activity->get(Activity\Object::ROUTEID) ? $Factory->route($this->Activity->get(Activity\Object::ROUTEID)) : null;
		$this->Sport = $Factory->sport($this->Activity->sportid());
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
		return !is_null($this->Trackdata);
	}

	/**
	 * @return boolean
	 */
	public function hasRoute() {
		return !is_null($this->Route);
	}
}