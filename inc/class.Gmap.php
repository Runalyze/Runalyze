<?php
/**
 * This file contains the class::GMap for displaying GoogleMaps
 */

Config::register('Training', 'TRAINING_MAP_COLOR', 'string', '#FF5500', 'Linienfarbe auf GoogleMaps-Karte (#RGB)');
Config::register('Training', 'TRAINING_MAP_MARKER', 'bool', true, 'Kilometer-Markierungen anzeigen');
Config::register('Training', 'TRAINING_MAPTYPE', 'select',
	array('G_NORMAL_MAP' => false, 'G_HYBRID_MAP' => true, 'G_SATELLITE_MAP' => false, 'G_PHYSICAL_MAP' => false), 'Typ der GoogleMaps-Karte',
	array('Normal', 'Hybrid', 'Sattelit', 'Physikalisch'));

/**
 * Class: GMap
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
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
		$this->StringID   = 'map_'.$TrainingId;
		$this->GpsData    = $GpsData;
	}

	/**
	 * Display 
	 */
	public function displayMap() {
		$Code  = $this->getCodeForInit();
		$Code .= $this->getCodeForPolylines();
		$Code .= $this->getCodeForKmMarker();
		
		echo Ajax::wrapJSasFunction($Code);
		echo '<div id="'.$this->StringID.'" class="map"></div>';
	}

	/**
	 * Get code to init map
	 * @return string
	 */
	protected function getCodeForInit() {
		return 'RunalyzeGMap.setMapType("'.CONF_TRAINING_MAPTYPE.'")'.
					'.setPolylineOptions({strokeColor:"'.CONF_TRAINING_MAP_COLOR.'"})'.
					'.init("#'.$this->StringID.'");';
	}

	/**
	 * Get JS-code for adding polyline(s)
	 * @return string
	 */
	protected function getCodeForPolylines() {
		$Code = '';
		$Path = array();

		$this->GpsData->startLoop();
		while ($this->GpsData->nextStep()) {
			$PointData = addslashes(Helper::Km($this->GpsData->getDistance(),2).'<br />'.Helper::Time($this->GpsData->getTime(), false, 2));

			if ($this->GpsData->getCalculatedDistanceOfStep() > self::$MAXIMUM_DISTANCE_OF_STEP) {
				$Code .= 'RunalyzeGMap.addPolyline(['.implode(',', $Path).']);';
				$Path  = array();
			}

			$Path[] = '['.$this->GpsData->getLatitude().','.$this->GpsData->getLongitude().',"'.$PointData.'"]';
		}

		$Code .= 'RunalyzeGMap.addPolyline(['.implode(',', $Path).']);';

		return $Code;
	}

	/**
	 * Get JS-code for adding marker(s)
	 * @return string
	 */
	protected function getCodeForKmMarker() {
		if (!CONF_TRAINING_MAP_MARKER)
			return '';
		
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
			if ($km%5 == 0 && $km <= 50)
				return 'img/marker/marker-'.($km%5).'.png';
			else
				return 'img/marker/point.gif';
		} else {
			return 'img/marker/marker-'.$km.'.png';
		}
	}
}
?>