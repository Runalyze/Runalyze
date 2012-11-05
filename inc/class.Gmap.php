<?php
/**
 * Class: GMap
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Gmap {
	/**
	 * Maxmium distance betweent two points of a line
	 * @var double
	 */
	static private $MAXIMUM_DISTANCE_OF_STEP = 0.05;

	/**
	 * String for html-id
	 * @var string
	 */
	protected $StringID = '';

	/**
	 * ID of displayed training
	 * @var int
	 */
	protected $TrainingId = -1;

	/**
	 * Internal GpsData-object
	 * @var GpsData
	 */
	protected $GpsData;

	/**
	 * Constructor
	 * @param int $TrainingId
	 * @param GpsData $GpsData
	 */
	public function __construct($TrainingId, $GpsData) {
		$this->TrainingId = $TrainingId;
		$this->StringID   = self::getStringIDfor($TrainingId);
		$this->GpsData    = $GpsData;
	}

	/**
	 * Set cache
	 * @param GpsDataCache $CacheObject 
	 */
	public function setCacheTo(GpsDataCache &$CacheObject) {
		$CacheObject->set('marker', $this->getCodeForKmMarker());

		$Codes = $this->getCodeForPolylines(true, true);
		$CacheObject->set('polylines', $Codes[0]);
		$CacheObject->set('polylines_without_hover', $Codes[1]);
	}

	/**
	 * Get string ID for a given training ID
	 * @param int $TrainingID
	 * @return string 
	 */
	static public function getStringIDfor($TrainingID) {
		return 'map_'.$TrainingID;
	}

	/**
	 * Display 
	 */
	public function displayMap() {
		$this->outputJScode();
		$this->outputHTML();
	}

	/**
	 * Output HTML-code 
	 */
	public function outputHTML() {
		include 'tpl/tpl.Gmap.toolbar.php';
	}

	/**
	 * Output JavaScript-Code 
	 */
	protected function outputJScode() {
		$Code  = $this->getCodeForInit();
		$Code .= $this->getCodeForPolylines();
		$Code .= $this->getCodeForKmMarker();

		echo Ajax::wrapJSasFunction($Code);
	}

	/**
	 * Get code to init map
	 * @return string
	 */
	public function getCodeForInit() {
		return 'RunalyzeGMap.setMapType("'.CONF_TRAINING_MAPTYPE.'")'.
					'.setPolylineOptions({strokeColor:"'.CONF_TRAINING_MAP_COLOR.'"})'.
					'.init("#'.$this->StringID.'");';
	}

	/**
	 * Get JS-code for adding polyline(s)
	 * @return string
	 */
	public function getCodeForPolylines($withoutHover = false, $both = false) {
		if (!$this->GpsData->getCache()->isEmpty()) {
			if ($withoutHover)
				return $this->GpsData->getCache()->get('polylines_without_hover');
			else
				return $this->GpsData->getCache()->get('polylines');
		}

		if (!$this->GpsData->hasPositionData())
			return $both ? array('', '') : '';

		$Code = '';
		$CodeWith = '';
		$CodeWithout = '';
		$Path = array();

		$AvgPace = $this->GpsData->getAveragePace();
		if ($AvgPace > 0 && (15/$AvgPace) > self::$MAXIMUM_DISTANCE_OF_STEP)
			self::$MAXIMUM_DISTANCE_OF_STEP = 15 / $AvgPace;

		$this->GpsData->startLoop();
		$this->GpsData->setStepSize(5);
		self::$MAXIMUM_DISTANCE_OF_STEP *= 5;

		while ($this->GpsData->nextStep()) {
			$Lat = $this->GpsData->getLatitude();
			$Lon = $this->GpsData->getLongitude();
			if ($Lat == 0 && $Lon == 0)
				continue;

			if ($withoutHover && !$both)
				$PointData = '';
			else
				$PointData = addslashes(Running::Km($this->GpsData->getDistance(),2).'<br />'.Time::toString($this->GpsData->getTime(), false, 2));

			// TODO: Try to find such pauses in a different way - this is not the fastest one
			if ($this->GpsData->getCalculatedDistanceOfStep() > self::$MAXIMUM_DISTANCE_OF_STEP) {
				$PathString = implode(',', $Path);
				if ($both) {
					$CodeWith    .= 'RunalyzeGMap.addPolyline(['.$PathString.']);';
					$CodeWithout .= 'RunalyzeGMap.addPolyline(['.$PathString.'],true);';
				} else
					$Code        .= 'RunalyzeGMap.addPolyline(['.$PathString.']'.($withoutHover?',true':'').');';
				$Path  = array();
			}

			$Path[] = '['.$Lat.','.$Lon.',"'.$PointData.'"]';
		}

		$PathString = implode(',', $Path);
		if ($both) {
			$CodeWith    .= 'RunalyzeGMap.addPolyline(['.$PathString.']);';
			$CodeWithout .= 'RunalyzeGMap.addPolyline(['.$PathString.'],true);';

			return array($CodeWith, $CodeWithout);
		} else
			$Code        .= 'RunalyzeGMap.addPolyline(['.$PathString.']'.($withoutHover?',true':'').');';

		return $Code;
	}

	/**
	 * Get JS-code for adding marker(s)
	 * @return string
	 */
	protected function getCodeForKmMarker() {
		if (!$this->GpsData->getCache()->isEmpty()) {
			return $this->GpsData->getCache()->get('marker');
		}

		if (!$this->GpsData->hasPositionData())
			return '';

		$SportDat = Mysql::getInstance()->fetchSingle('SELECT `sportid` FROM '.PREFIX.'training WHERE `id`='.$this->TrainingId);
		$SportId  = $SportDat['sportid'];
		$Code     = '';
		$Marker   = array();

		$this->GpsData->startLoop();

		if ($this->GpsData->getLatitude() > 0 && $this->GpsData->getLongitude() > 0)
			$Marker[] = '{lat:'.$this->GpsData->getLatitude().',lng:'.$this->GpsData->getLongitude().',data:"Start",options:{icon:"'.$this->getIconForMarker().'"}}';

		while ($this->GpsData->nextKilometer()) {
			$MarkerData = addslashes(Running::Km($this->GpsData->getDistance()).'<br />'.strip_tags(Running::Speed($this->GpsData->getDistanceOfStep(), $this->GpsData->getTimeOfStep(), $SportId)));
			$Marker[]   = '{lat:'.$this->GpsData->getLatitude().',lng:'.$this->GpsData->getLongitude().',data:"'.$MarkerData.'",options:{icon:"'.$this->getIconForMarker().'"}}';
		}

		$Code .= 'RunalyzeGMap.addMarkers(['.implode(',', $Marker).']);';

		if (!CONF_TRAINING_MAP_MARKER)
			$Code .= 'RunalyzeGMap.hideMarkers();';

		return $Code;
	}

	/**
	 * Get icon for current marker
	 * @return string
	 */
	protected function getIconForMarker() {
		$TotalDistance = $this->GpsData->getTotalDistance();
		$km = $this->GpsData->getDistance();

		if ($km < 0.5)
			return 'img/marker/start.png';
		if ($TotalDistance == $km)
			return 'img/marker/finish.png';

		$km = round($km);

		if ($TotalDistance > 20) {
			if ($km%5 == 0 && $km <= 95)
				return 'img/marker/marker-'.$km.'.png';
			else
				return 'img/marker/point.gif';
		} else {
			return 'img/marker/marker-'.$km.'.png';
		}
	}
}