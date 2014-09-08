<?php
/**
 * This file contains class::LeafletRoute
 * @package Runalyze\Data\GPS
 */
/**
 * Leaflet-route
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS
 */
class LeafletTrainingRoute extends LeafletRoute {
	/**
	 * Gps data
	 * @var GpsData
	 */
	protected $GPS = null;

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
	 * @var int
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
	 * Boolean flag: add icons and info
	 * @var bool
	 */
	protected $addIconsAndInfo = true;

	/**
	 * Construct new route
	 * @param string $id
	 * @param GpsData $GpsData
	 * @param bool $addIconsAndInfo [optional]
	 */
	public function __construct($id, GpsData $GpsData, $addIconsAndInfo = true) {
		$this->id = $id;
		$this->GPS = $GpsData;
		$this->addIconsAndInfo = $addIconsAndInfo;

		$this->createRoute();
	}

	/**
	 * Create route
	 */
	protected function createRoute() {
		$this->prepareLoop();
		$this->findLimitForPauses();
		$this->fillCurrentSegment();

		while ($this->GPS->nextStep()) {
			$this->checkForPause();
			$this->fillCurrentSegment();
			$this->checkForDistanceMarker();
		}

		$this->addCurrentSegment();
		$this->addStartAndEndIcon();
	}

	/**
	 * Prepare loop
	 */
	protected function prepareLoop() {
		$this->GPS->startLoop();
		$this->GPS->setStepSize((int)CONF_GMAP_PATH_PRECISION);
	}

	/**
	 * Find limit for pauses
	 */
	protected function findLimitForPauses() {
		$SecondsForDist = (CONF_GMAP_PATH_BREAK != 'no') ? (int)CONF_GMAP_PATH_BREAK : 15;
		$AvgPace        = $this->GPS->getAveragePace();

		if ($AvgPace > 0 && ($SecondsForDist/$AvgPace) > $this->PauseLimit)
			$this->PauseLimit = $SecondsForDist / $AvgPace;

		$this->PauseLimit *= (int)CONF_GMAP_PATH_PRECISION;
	}

	/**
	 * Check for pause
	 */
	protected function checkForPause() {
		if (CONF_GMAP_PATH_BREAK != 'no' && $this->GPS->getCalculatedDistanceOfStep() > $this->PauseLimit)
			$this->addCurrentSegment();
	}

	/**
	 * Fill current segment
	 */
	protected function fillCurrentSegment() {
		$this->Path[] = array((float)$this->GPS->getLatitude(), (float)$this->GPS->getLongitude());

		if (!$this->addIconsAndInfo)
			return;

		$Infos = array();
		$Infos['km'] = (float)$this->GPS->getDistance();
		$Infos[__('Distance')] = Running::Km($this->GPS->getDistance(), 2);

		if ($this->GPS->hasTimeData())
			$Infos[__('Time')] = Time::toString($this->GPS->getTime(), false, false, false);

		$this->Info[] = $Infos;
	}

	/**
	 * Check for distance marker
	 */
	protected function checkForDistanceMarker() {
		if (!$this->addIconsAndInfo)
			return;

		if (round($this->GPS->getDistance(), 2) >= $this->Dist) {
			$this->addMarker(
				$this->GPS->getLatitude(),
				$this->GPS->getLongitude(),
				$this->distIcon($this->Dist),
				sprintf( __('<strong>%s. km</strong> in %s'), $this->Dist, SportSpeed::minPerKm(1, $this->GPS->getTime() - $this->Time) ).'<br>'.
				sprintf( __('<strong>Time:</strong> %s'), Time::toString($this->GPS->getTime(), false, false, false) )
			);

			$this->Time = $this->GPS->getTime();
			$this->Dist += 1;
		}
	}

	/**
	 * Add start and end icon
	 */
	protected function addStartAndEndIcon() {
		if (!$this->addIconsAndInfo)
			return;

		$this->addMarker(
			$this->Paths[0][0][0],
			$this->Paths[0][0][1],
			$this->startIcon(),
			__('Start')
		);

		$this->addMarker(
			$this->GPS->getLatitude(),
			$this->GPS->getLongitude(),
			$this->endIcon(),
			sprintf( __('<strong>Total:</strong> %s'), Running::Km($this->GPS->getDistance(), 2) ).'<br>'.
			sprintf( __('<strong>Time:</strong> %s'), Time::toString($this->GPS->getTime(), false, false, false) )
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
}