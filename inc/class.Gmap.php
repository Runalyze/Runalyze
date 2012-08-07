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
	public function getCodeForPolylines($withoutHover = false) {
		$Code = '';
		$Path = array();

		$AvgPace = $this->GpsData->getAveragePace();
		if ($AvgPace > 0 && (15/$AvgPace) > self::$MAXIMUM_DISTANCE_OF_STEP)
			self::$MAXIMUM_DISTANCE_OF_STEP = 15 / $AvgPace;

		$this->GpsData->startLoop();
		while ($this->GpsData->nextStep()) {
			if ($this->GpsData->getLatitude() == 0)
				continue;

			if ($withoutHover)
				$PointData = '';
			else
				$PointData = addslashes(Helper::Km($this->GpsData->getDistance(),2).'<br />'.Helper::Time($this->GpsData->getTime(), false, 2));

			// TODO: Try to find such pauses in a different way - this is not the fastest one
			if ($this->GpsData->getCalculatedDistanceOfStep() > self::$MAXIMUM_DISTANCE_OF_STEP) {
				$Code .= 'RunalyzeGMap.addPolyline(['.implode(',', $Path).']'.($withoutHover?',true':'').');';
				$Path  = array();
			}

			$Path[] = '['.$this->GpsData->getLatitude().','.$this->GpsData->getLongitude().',"'.$PointData.'"]';
		}

		$Code .= 'RunalyzeGMap.addPolyline(['.implode(',', $Path).']'.($withoutHover?',true':'').');';

		return $Code;
	}

	/**
	 * Get JS-code for adding marker(s)
	 * @return string
	 */
	protected function getCodeForKmMarker() {
		$Training = new Training($this->TrainingId);
		$SportId  = $Training->get('sportid');
		$Code     = '';
		$Marker   = array();

		$this->GpsData->startLoop();
		$Marker[] = '{lat:'.$this->GpsData->getLatitude().',lng:'.$this->GpsData->getLongitude().',data:"Start<br />'.$Training->getDate().'",options:{icon:"'.$this->getIconForMarker().'"}}';

		while ($this->GpsData->nextKilometer()) {
			$MarkerData = addslashes(Helper::Km($this->GpsData->getDistance()).'<br />'.strip_tags(Helper::Speed($this->GpsData->getDistanceOfStep(), $this->GpsData->getTimeOfStep(), $SportId)));
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

		if ($km < 0.2)
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