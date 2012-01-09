<?php
/**
 * This file contains the class::ImporterFITLOG for importing a training from FITLOG
 */
/**
 * Class: ImporterFITLOG
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 */
class ImporterFITLOG extends Importer {
	/**
	 * Parsed XML as array
	 * @var array
	 */
	private $XML;

	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		$FileContent = $this->getFileContentAsString();
		$Parser      = new XmlParser($FileContent);
		$XML         = $Parser->getContentAsArray();
		if (!isset($XML['fitnessworkbook']) || !isset($XML['fitnessworkbook']['athletelog'])) {
			$this->addError('Es scheint keine Fitlog-Trainingsdatei zu sein.');
			return;
		}

		$this->XML   = $XML['fitnessworkbook']['athletelog'];

		$this->parseXML();
	}

	/**
	 * Parse xml
	 */
	private function parseXML() {
		$time      = strtotime($this->XML['activity']['attr']['StartTime']);
		$this->XML = $this->XML['activity'];

		$this->set('sportid', CONF_RUNNINGSPORT);
		$this->set('datum', date("d.m.Y", $time));
		$this->set('zeit', date("H:i", $time));
		$this->set('time', $time);

		$this->parseLaps();
		$this->parseTrack();
		$this->parseOptionalValues();
	}

	/**
	 * Parse track
	 */
	private function parseTrack() {
		if (!isset($this->XML['track']))
			return;
		
		$time      = array();
		$elevation = array();
		$heartrate = array();
		$latitude  = array();
		$longitude = array();
		$distance  = array();
		$pace      = array();

		$Laps = $this->XML['track']['pt'];
		foreach ($Laps as $i => $Point) {
			if (isset($Point['attr']['lat'])) {
				$lat  = round($Point['attr']['lat'], 7);
				$lon  = round($Point['attr']['lon'], 7);
				$dist = $i==0 ? 0 : round(GpsData::distance($lat, $lon, $latitude[$i-1], $longitude[$i-1]), 3);
			} elseif (isset($latitude[$i-1])) {
				$lat  = $latitude[$i-1];
				$lon  = $longitude[$i-1];
				$dist = 0;
			} else
				continue;

			$time[]      = $Point['attr']['tm'];
			$elevation[] = (isset($Point['attr']['ele'])) ? round($Point['attr']['ele']) : 0;
			$heartrate[] = (isset($Point['attr']['hr'])) ? $Point['attr']['hr'] : 0;
			$latitude[]  = $lat;
			$longitude[] = $lon;
			$distance[]  = ($i==0) ? $dist : end($distance) + $dist;
			$pace[]      = ((end($distance) - prev($distance)) != 0)
				? round((end($time) - prev($time)) / (end($distance) - prev($distance)))
				: 0;
		}

		if (!empty($heartrate)) {
			$this->set('pulse_avg', round(array_sum($heartrate)/count($heartrate)));
			$this->set('pulse_max', max($heartrate));
		}

		$this->setArrayForTime($time);
		$this->setArrayForLatitude($latitude);
		$this->setArrayForLongitude($longitude);
		$this->setArrayForElevation($elevation);
		$this->setArrayForDistance($distance);
		$this->setArrayForHeartrate($heartrate);
		$this->setArrayForPace($pace);
	}

	/**
	 * Parse laps
	 */
	private function parseLaps() {
		if (!isset($this->XML['laps']))
			return;

		$Distance = 0;
		$Calories = 0;
		$Laps = $this->XML['laps']['lap'];
		foreach ($Laps as $i => $Lap) {
			if (isset($Lap['distance']))
				$Distance += $Lap['distance']['attr']['TotalMeters'];
			if (isset($Lap['calories']))
				$Calories += $Lap['calories']['attr']['TotalCal'];
		}

		if ($Distance > 0)
			$this->set('distance', round($Distance)/1000);
		if ($Calories > 0)
			$this->set('kcal', $Calories);
	}

	/**
	 * Parse optional values, may overwrite previous distance and so on
	 */
	private function parseOptionalValues() {
		if (isset($this->XML['duration']))
			$this->set('s', round($this->XML['duration']['attr']['TotalSeconds']));

		if (isset($this->XML['distance']))
			$this->set('distance', round($this->XML['distance']['attr']['TotalMeters'])/1000);

		if (isset($this->XML['calories']))
			$this->set('kcal', $this->XML['calories']['attr']['TotalCal']);

		if (isset($this->XML['location']))
			$this->set('route', $this->XML['location']['attr']['Name']);
	}
}
?>