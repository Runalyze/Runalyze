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
		$XML = $this->getFileContentAsXml();

		if (!$XML)
			return;

		if (empty($XML->AthleteLog)) {
			$this->addError('Es scheint keine Fitlog-Trainingsdatei zu sein.');
			return;
		}

		$this->XML   = $XML->AthleteLog;
		$this->parseXML();
		$this->setCreatorToFileUpload();
	}

	/**
	 * Parse xml
	 */
	private function parseXML() {
		$this->XML = $this->XML->Activity;
		$time      = strtotime((string)$this->XML['StartTime']);

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
		if (empty($this->XML->Track))
			return;

		$time      = array();
		$elevation = array();
		$heartrate = array();
		$latitude  = array();
		$longitude = array();
		$distance  = array();
		$pace      = array();
		$i         = 0;

		foreach ($this->XML->Track->pt as $Point) {
			if (!empty($Point['lat'])) {
				$lat  = round((double)$Point['lat'], 7);
				$lon  = round((double)$Point['lon'], 7);
				$dist = $i==0 ? 0 : round(GpsData::distance($lat, $lon, $latitude[$i-1], $longitude[$i-1]), 3);
			} elseif (isset($latitude[$i-1])) {
				$lat  = $latitude[$i-1];
				$lon  = $longitude[$i-1];
				$dist = 0;
			} else
				continue;

			$time[]      = (int)$Point['tm'];
			$elevation[] = (!empty($Point['ele'])) ? round((int)$Point['ele']) : 0;
			$heartrate[] = (!empty($Point['hr'])) ? (int)$Point['hr'] : 0;
			$latitude[]  = $lat;
			$longitude[] = $lon;
			$distance[]  = ($i==0) ? $dist : end($distance) + $dist;
			$pace[]      = ((end($distance) - prev($distance)) != 0)
				? round((end($time) - prev($time)) / (end($distance) - prev($distance)))
				: 0;

			$i++;
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
		if (empty($this->XML->Laps))
			return;

		$Distance = 0;
		$Calories = 0;
		foreach ($this->XML->Laps->children() as $Lap) {
			if (!empty($Lap->Distance['TotalMeters']))
				$Distance += (int)$Lap->Distance['TotalMeters'];
			if (!empty($Lap->Distance['TotalMeters']))
				$Calories += (int)$Lap->Calories['TotalCal'];
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
		if (!empty($this->XML->Duration['TotalSeconds']))
			$this->set('s', round((double)$this->XML->Duration['TotalSeconds']));

		if (!empty($this->XML->Distance['TotalMeters']))
			$this->set('distance', round((double)$this->XML->Distance['TotalMeters'])/1000);

		if (!empty($this->XML->Calories['TotalCal']))
			$this->set('kcal', (int)$this->XML->Calories['TotalCal']);

		if (!empty($this->XML->Location['Name']))
			$this->set('route', (string)$this->XML->Location['Name']);

		if (!empty($this->XML->Weather['Temp']))
			$this->set('temperature', (int)$this->XML->Weather['Temp']);

		if ($this->get('pulse_max') == 0 && !empty($this->XML->HeartRateMaximumBPM))
			$this->set('pulse_max', (int)$this->XML->HeartRateMaximumBPM);

		if ($this->get('pulse_avg') == 0 && !empty($this->XML->HeartRateAverageBPM))
			$this->set('pulse_avg', (int)$this->XML->HeartRateAverageBPM);
	}
}