<?php
/**
 * This file contains class::FakeContext
 * @package Runalyze\View\Activity
 */

namespace Runalyze\View\Activity;

use Runalyze\Model\Activity;
use Runalyze\Model\HRV;
use Runalyze\Model\Route;
use Runalyze\Model\Sport;
use Runalyze\Model\Swimdata;
use Runalyze\Model\Trackdata;

/**
 * Fake activity context
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity
 */
class FakeContext extends \Runalyze\View\Activity\Context {
	/**
	 * @param \Runalyze\Model\Activity\Entity $activity
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param \Runalyze\Model\Swimdata\Entity $swimdata
	 * @param \Runalyze\Model\Route\Entity $route
	 * @param \Runalyze\Model\HRV\Entity $hrv
	 * @param \Runalyze\Model\Sport\Entity $sport
	 */
	public function __construct(
		Activity\Entity $activity,
		Trackdata\Entity $trackdata,
		Swimdata\Entity $swimdata,
		Route\Entity $route,
		HRV\Entity $hrv,
		Sport\Entity $sport
	) {
		$this->Activity = $activity;
		$this->Trackdata = $trackdata;
		$this->Swimdata = $swimdata;
		$this->Route = $route;
		$this->HRV = $hrv;
		$this->Sport = $sport;
		
		$this->Swimdata->fillDistanceArray($this->Trackdata);
		$this->Swimdata->fillSwolfArray($this->Trackdata);
		$this->Dataview = new Dataview($this->Activity);
	}

	/**
	 * @param \Runalyze\Model\Activity\Entity $activity
	 * @return \Runalyze\View\Activity\FakeContext
	 */
	public static function onlyWithActivity(Activity\Entity $activity)
	{
		return new FakeContext(
			$activity,
			new Trackdata\Entity(array()),
			new Swimdata\Entity(array()),
			new Route\Entity(array()),
			new HRV\Entity(array()),
			new Sport\Entity(array())
		);
	}

	/**
	 * @param \Runalyze\Model\Activity\Entity $activity
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param \Runalyze\Model\Route\Entity $route
	 * @return \Runalyze\View\Activity\FakeContext
	 */
	public static function withDefaultSport(
		Activity\Entity $activity,
		Trackdata\Entity $trackdata = null,
		Route\Entity $route = null
	) {
		if (null === $trackdata) {
			$trackdata = new Trackdata\Entity(array());
		}

		if (null === $route) {
			$route = new Route\Entity(array());
		}

		return new FakeContext(
			$activity,
			$trackdata,
			new Swimdata\Entity(array()),
			$route,
			new HRV\Entity(array()),
			new Sport\Entity(array(
				Sport\Entity::NAME => 'Sport',
				Sport\Entity::PACE_UNIT => \Runalyze\Parameter\Application\PaceUnit::KM_PER_H,
				Sport\Entity::HAS_DISTANCES => true,
				Sport\Entity::IS_OUTSIDE => true
			))
		);
	}

	/**
	 * @return \Runalyze\View\Activity\FakeContext[]
	 */
	public static function examplaryContexts()
	{
		return array(
			self::emptyContext(),
			self::indoorContext(),
			self::outdoorContext()
		);
	}

	/**
	 * @return \Runalyze\View\Activity\FakeContext
	 */
	public static function emptyContext()
	{
		return new FakeContext(
			new Activity\Entity(array()),
			new Trackdata\Entity(array()),
			new Swimdata\Entity(array()),
			new Route\Entity(array()),
			new HRV\Entity(array()),
			new Sport\Entity(array())
		);
	}

	/**
	 * @return \Runalyze\View\Activity\FakeContext
	 */
	public static function indoorContext()
	{
		return new FakeContext(
			new Activity\Entity(array(
				Activity\Entity::TIMESTAMP => time(),
				Activity\Entity::TIMESTAMP_CREATED => time(),
				Activity\Entity::TIME_IN_SECONDS => 600,
				Activity\Entity::CALORIES => 120,
				Activity\Entity::ELAPSED_TIME => 650,
				Activity\Entity::HR_AVG => 140,
				Activity\Entity::HR_MAX => 160,
				Activity\Entity::TRIMP => 50
			)),
			new Trackdata\Entity(array(
				Trackdata\Entity::TIME => array(100, 200, 300, 400, 500, 600),
				Trackdata\Entity::HEARTRATE => array(120, 130, 140, 140, 150, 160)
			)),
			new Swimdata\Entity(array()),
			new Route\Entity(array()),
			new HRV\Entity(array()),
			new Sport\Entity(array(
				Sport\Entity::NAME => 'Sport',
				Sport\Entity::PACE_UNIT => \Runalyze\Parameter\Application\PaceUnit::KM_PER_H,
				Sport\Entity::HAS_DISTANCES => false,
				Sport\Entity::IS_OUTSIDE => false
			))
		);
	}

	/**
	 * @return \Runalyze\View\Activity\FakeContext
	 */
	public static function outdoorContext()
	{
		return new FakeContext(
			new Activity\Entity(array(
				Activity\Entity::TIMESTAMP => time(),
				Activity\Entity::TIMESTAMP_CREATED => time(),
				Activity\Entity::TIME_IN_SECONDS => 600,
				Activity\Entity::DISTANCE => 2.0,
				Activity\Entity::CALORIES => 120,
				Activity\Entity::ELAPSED_TIME => 650,
				Activity\Entity::HR_AVG => 140,
				Activity\Entity::HR_MAX => 160,
				Activity\Entity::TRIMP => 50,
				Activity\Entity::CADENCE => 90
			)),
			new Trackdata\Entity(array(
				Trackdata\Entity::TIME => array(100, 200, 300, 400, 500, 600),
				Trackdata\Entity::HEARTRATE => array(120, 130, 140, 140, 150, 160),
				Trackdata\Entity::DISTANCE => array(0.3, 0.6, 0.9, 1.25, 1.6, 2.0),
				Trackdata\Entity::CADENCE => array(90, 90, 90, 90, 90, 90)
			)),
			new Swimdata\Entity(array()),
			new Route\Entity(array(
				Route\Entity::NAME => 'Some route',
				Route\Entity::DISTANCE => 2.0,
				Route\Entity::ELEVATION => 20,
				Route\Entity::ELEVATION_UP => 20,
				Route\Entity::ELEVATION_DOWN => 20,
				Route\Entity::ELEVATIONS_ORIGINAL => array(100, 105, 110, 120, 115, 100),
				Route\Entity::GEOHASHES => array('u0v90mey4wr9', 'u0v90rp9092c', 'u0v90xh7kdtg', 'u0v90ze0s5qq', 'u0v92b708c70', 'u0v92chhefmz')
			)),
			new HRV\Entity(array()),
			new Sport\Entity(array(
				Sport\Entity::NAME => 'Sport',
				Sport\Entity::PACE_UNIT => \Runalyze\Parameter\Application\PaceUnit::KM_PER_H,
				Sport\Entity::HAS_DISTANCES => true,
				Sport\Entity::IS_OUTSIDE => true
			))
		);
	}
}
