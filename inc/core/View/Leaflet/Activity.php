<?php
/**
 * This file contains class::Activity
 * @package Runalyze\View\Leaflet
 */

namespace Runalyze\View\Leaflet;

use Runalyze\View\Leaflet\Route as LeafletRoute;
use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;
use League\Geotools\Geotools;

/**
 * Leaflet route for an activity
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Leaflet
 */
class Activity extends LeafletRoute {
	/**
	 * Route
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Route;

	/**
	 * Route loop
	 * @var \Runalyze\Model\Route\Loop
	 */
	protected $RouteLoop;

	/**
	 * Trackdata
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata = null;

	/**
	 * Trackdata loop
	 * @var \Runalyze\Model\Trackdata\Loop
	 */
	protected $TrackdataLoop = null;

	/**
	 * Path
	 * @var array
	 */
	protected $Path = array();

	/**
	 * Info
	 * @var array
	 */
	protected $Info = array();

	/**
	 * Distance
	 * @var int km or miles
	 */
	protected $Dist = 1;

	/**
	 * Time
	 * @var int
	 */
	protected $Time = 0;

	/**
	 * Pause limit
	 * @var float distance in km
	 */
	protected $PauseLimit = 0.05;

	/**
	 * @var int
	 */
	protected $PauseIndex = 0;

	/**
	 * @var bool
	 */
	protected $PathShouldBreak = false;

	/**
	 * Boolean flag: add icons and info
	 * @var bool
	 */
	protected $addIconsAndInfo = true;

	/**
	 * Construct new route
	 * @param string $id
	 * @param \Runalyze\Model\Route\Entity $route
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata [optional]
	 * @param bool $addIconsAndInfo [optional]
	 * @throws \InvalidArgumentException
	 */
	public function __construct($id, Route\Entity $route, Trackdata\Entity $trackdata = null, $addIconsAndInfo = true) {
		parent::__construct($id);

		if (!$route->hasPositionData()) {
			throw new \InvalidArgumentException('Route needs position data.');
		}

		$this->Route = $route;
		$this->Trackdata = $trackdata;
		$this->addIconsAndInfo = $addIconsAndInfo;

        $this->addOption('options',array('color' => Configuration::ActivityView()->RouteColor()));

		$this->createRoute();
	}

	/**
	 * Create route
	 */
	protected function createRoute() {
		$this->prepareLoops();
		$this->findLimitForPauses();
		$this->fillCurrentSegment();

		while ($this->nextStep()) {
			$this->checkForPause();
			$this->fillCurrentSegment();
			$this->checkForDistanceMarker();

			if ($this->PathShouldBreak) {
				$this->addCurrentSegment();
				$this->PathShouldBreak = false;
			}
		}

		$this->addCurrentSegment();
		$this->addStartAndEndIcon();
		$this->defineInfoLabels();
	}

	/**
	 * Prepare loops
	 */
	protected function prepareLoops() {
		$stepSize = (int)Configuration::ActivityView()->routePrecision()->value();

		$this->RouteLoop = new Route\Loop($this->Route);
		$this->RouteLoop->setStepSize($stepSize);

		if (
			!is_null($this->Trackdata)
			&& ($this->Route->num() == $this->Trackdata->num())
			&& $this->Trackdata->has(Trackdata\Entity::TIME)
		) {
			$this->TrackdataLoop = new Trackdata\Loop($this->Trackdata);
			$this->TrackdataLoop->setStepSize($stepSize);
		}
	}

	/**
	 * Has trackdata loop?
	 * @return boolean
	 */
	protected function hasTrackdataLoop() {
		return !is_null($this->TrackdataLoop);
	}

	/**
	 * Next step
	 * @return boolean
	 */
	protected function nextStep() {
		if ($this->hasTrackdataLoop()) {
			$this->TrackdataLoop->nextStep();
		}

		return $this->RouteLoop->nextStep();
	}

	/**
	 * Fill current segment
	 */
	protected function fillCurrentSegment() {
		$geohash = (new Geotools())->geohash()->decode($this->RouteLoop->geohash());
		if ($this->RouteLoop->geohash() == '7zzzzzzzzzzz') {
			return;
		}

		$this->Path[] = array((float)$geohash->getCoordinate()->getLatitude(), (float)$geohash->getCoordinate()->getLongitude());

		if ($this->addIconsAndInfo && $this->hasTrackdataLoop()) {
			$this->Info[] = array(
				(float)$this->TrackdataLoop->distance(),
				(int)$this->TrackdataLoop->time()
			);
		}
	}

	/**
	 * Define labels
	 */
	protected function defineInfoLabels() {
		if ($this->addIconsAndInfo && $this->hasTrackdataLoop()) {
			$this->InfoLabels = array(
				__('Distance'),
				__('Time')
			);
			$this->InfoFunctions = array(
				'function(v){return (v*'.Configuration::General()->distanceUnitSystem()->distanceToPreferredUnitFactor().').toFixed(2)+"&nbsp;'.Configuration::General()->distanceUnitSystem()->distanceUnit().'";}',
				'function(v){return (new Date(v * 1000)).toUTCString().match(/(\d\d:\d\d:\d\d)/)[0];}'
			);
		}
	}

	/**
	 * Check for distance marker
	 */
	protected function checkForDistanceMarker() {
		if (!$this->addIconsAndInfo || !$this->hasTrackdataLoop()) {
			return;
		}

		if (round((new Distance($this->TrackdataLoop->distance()))->valueInPreferredUnit(), 2) >= $this->Dist) {
			$Pace = new Pace($this->TrackdataLoop->time() - $this->Time);

			$Tooltip = sprintf( __('<strong>%s. %s</strong> in %s'), $this->Dist, Configuration::General()->distanceUnitSystem()->distanceUnit(), $Pace->asMinPerKm() ).'<br>';
			$Tooltip .= sprintf( __('<strong>Time:</strong> %s'), Duration::format($this->TrackdataLoop->time()) );

			$this->addMarkerGeohash(
				$this->RouteLoop->geohash(),
				$this->distIcon($this->Dist),
				$Tooltip
			);

			$this->Dist += 1;
			$this->Time = $this->TrackdataLoop->time();
		}
	}

	/**
	 * Add start and end icon
	 */
	protected function addStartAndEndIcon() {
		if (!$this->addIconsAndInfo) {
			return;
		}

		$this->addMarker(
			$this->Paths[0][0][0],
			$this->Paths[0][0][1],
			$this->startIcon(),
			__('Start')
		);

		if ($this->hasTrackdataLoop()) {
			$Tooltip = sprintf( __('<strong>Total:</strong> %s'), Distance::format($this->TrackdataLoop->distance()) );
			$Tooltip .= '<br>'.sprintf( __('<strong>Time:</strong> %s'), Duration::format($this->TrackdataLoop->time()) );
		} else {
			$Tooltip = '';
		}

		$this->addMarkerGeohash(
			$this->RouteLoop->geohash(),
			$this->endIcon(),
			$Tooltip
		);
	}

	/**
	 * Add current segment
	 */
	protected function addCurrentSegment() {
		$this->addSegment($this->Path, $this->Info);

		$this->Path = array();
		$this->Info = array();
	}

	/**
	 * Find limit for pauses
	 */
	protected function findLimitForPauses() {
		if (!is_null($this->Trackdata) && $this->Trackdata->has(Trackdata\Entity::DISTANCE)) {
			$SecondsForDist = (int)Configuration::ActivityView()->routeBreak()->value();
			$AveragePace = $this->Trackdata->totalDistance() > 0 ? $this->Trackdata->totalTime() / $this->Trackdata->totalDistance() : 0;

			if ($AveragePace > 0) {
				$this->PauseLimit = max($this->PauseLimit, $SecondsForDist/$AveragePace);
			}
		}

		$this->PauseLimit *= (int)Configuration::ActivityView()->routePrecision()->value();
	}

	/**
	 * Check for pause
	 */
	protected function checkForPause() {
		if (!is_null($this->Trackdata) && $this->Trackdata->hasPauses()) {
			if (
				$this->PauseIndex < $this->Trackdata->pauses()->num() &&
				$this->Trackdata->pauses()->at($this->PauseIndex)->time() <= $this->Trackdata->at($this->TrackdataLoop->index(), Trackdata\Entity::TIME)
			) {
				$this->addCurrentPauseIcon();

				$this->PathShouldBreak = true;
				$this->PauseIndex++;
			}
		} elseif (!Configuration::ActivityView()->routeBreak()->never() && $this->RouteLoop->calculatedStepDistance() > $this->PauseLimit) {
			$this->addCurrentSegment();
		}
	}

	/**
	 * Add icon for current pause
	 */
	protected function addCurrentPauseIcon() {
		$Pause = $this->Trackdata->pauses()->at($this->PauseIndex);
		$Index = $this->RouteLoop->index();

		$Tooltip = sprintf( __('<strong>Pause</strong> of %s'), Duration::format($Pause->duration()));
		$Tooltip .= '<br>'.sprintf( __('<strong>Distance:</strong> %s'), Distance::format($this->Trackdata->at($Index, Trackdata\Entity::DISTANCE)) );
		$Tooltip .= '<br>'.sprintf( __('<strong>Time:</strong> %s'), Duration::format($this->Trackdata->at($Index, Trackdata\Entity::TIME)) );

		if ($Pause->hasHeartRateInfo()) {
			$Tooltip .= '<br>'.sprintf( __('<strong>Heart rate:</strong>').' '.__('%s to %s'), $Pause->hrStart(), $Pause->hrEnd().' bpm' );
		}

		$this->addMarkerGeohash(
			$this->Route->at($Index, Route\Entity::GEOHASHES),
			$this->pauseIcon(),
			$Tooltip
		);
	}
}
