<?php
/**
 * Parser for tcx-files from Garmin
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class ParserTCX extends Parser {
	/**
	 * Complete XML
	 * @var SimpleXMLElement
	 */
	private $CompleteXML = null;

	/**
	 * XML
	 * @var SimpleXMLElement
	 */
	private $XML = null;

	/**
	 * Current index of training
	 * @var int 
	 */
	private $currentTraining = 0;

	/**
	 * Number of trainings
	 * @var int
	 */
	private $numberOfTrainings = 0;

	/**
	 * Starttime, can be changed for pauses
	 * @var int
	 */
	private $starttime = 0;

	/**
	 * Calories
	 * @var int
	 */
	private $calories = 0;

	/**
	 * Last point
	 * @var int
	 */
	private $lastPoint = 0;

	/**
	 * Boolean flag: Last point was empty
	 * @var boolean
	 */
	private $lastPointWasEmpty = false;

	/**
	 * Construct a new parser, needs XML
	 * @param SimpleXMLElement $XML
	 */
	public function __construct($XML) {
		$this->CompleteXML = simplexml_load_string_utf8($XML);

		if ($this->checkXML())
			$this->initXML();
	}

	/**
	 * Parse current training 
	 */
	public function parseTraining() {
		$this->initEmptyValues();
		$this->parseStarttime();
		$this->parseLaps();
		$this->setValues();
	}

	/**
	 * Check if XML is correct
	 * @return boolean 
	 */
	protected function checkXML() {
		if (!$this->CompleteXML instanceof SimpleXMLElement) {
			$this->addError('Keine XML-Datei gegeben.');
			return false;
		}

		if (is_null($this->CompleteXML->Activities->Activity)) {
			$this->addError('Die XML-Datei enth&auml;lt keine Trainings.');
			return false;
		}

		return true;
	}

	/**
	 * Are more than one training in this file?
	 * @return boolean
	 */
	public function hasMultipleTrainings() {
		return $this->numberOfTrainings > 1;
	}

	/**
	 * Get number of trainings
	 * @return int
	 */
	public function numberOfTrainings() {
		return $this->numberOfTrainings;
	}

	/**
	 * Init internal XML
	 */
	protected function initXML() {
		$this->currentTraining   = 0;
		$this->numberOfTrainings = count($this->CompleteXML->Activities->Activity);

		$this->setXMLfromIndex($this->currentTraining);
	}

	/**
	 * Set internal XML from index of training
	 * @param int $index 
	 */
	protected function setXMLfromIndex($index) {
		$this->XML = $this->CompleteXML->Activities->Activity[$index];
	}

	/**
	 * Go to next training if available
	 * @return boolean
	 */
	public function nextTraining() {
		if ($this->currentTraining < $this->numberOfTrainings) {
			$this->setXMLfromIndex($this->currentTraining);
			$this->parseTraining();
			$this->currentTraining++;

			return true;
		}

		return false;
	}

	/**
	 * Init all empty values 
	 */
	protected function initEmptyValues() {
		$this->starttime = 0;
		$this->calories  = 0;
		$this->data      = array(
			'laps_distance' => 0,
			'laps_time'     => 0,
			'time_in_s'     => array(),
			'latitude'      => array(),
			'longitude'     => array(),
			'altitude'      => array(),
			'km'            => array(),
			'heartrate'     => array(),
			'pace'          => array(),
			'splits'        => array());
	}

	/**
	 * Set all parsed values
	 */
	protected function setValues() {
		$this->setAllArrays();
		$this->setGeneralValues();
		$this->setOptionalValue();
	}

	/**
	 * Set general values
	 */
	protected function setGeneralValues() {
		$this->set('sportid', $this->getCurrentSportId());
		$this->set('kcal', $this->calories);
		$this->set('splits', implode('-', $this->data['splits']));
	}

	/**
	 * Set optional values
	 */
	protected function setOptionalValue() {
		if (!empty($this->data['km']))
			$this->set('distance', round(end($this->data['km']), 2));
		elseif ($this->data['laps_distance'] > 0)
			$this->set('distance', round($this->data['laps_distance'], 2));

		if (!empty($this->data['time_in_s']))
			$this->set('s', end($this->data['time_in_s']));
		elseif ($this->data['laps_time'] > 0)
			$this->set('s', $this->data['laps_time']);

		if (!empty($this->XML->Training))
			$this->set('comment', (string)$this->XML->Training->Plan->Name);
		else
			$this->set('comment', '');
	}

	/**
	 * Set all arrays
	 */
	protected function setAllArrays() {
		$this->setArrayForTime($this->data['time_in_s']);
		$this->setArrayForLatitude($this->data['latitude']);
		$this->setArrayForLongitude($this->data['longitude']);
		$this->setArrayForElevation($this->data['altitude']);
		$this->setArrayForDistance($this->data['km']);
		$this->setArrayForHeartrate($this->data['heartrate']);
		$this->setArrayForPace($this->data['pace']);
	}

	/**
	 * Parse starttime
	 */
	protected function parseStarttime() {
		$this->starttime = strtotime((string)$this->XML->Id);

		$this->set('time', $this->starttime);
		$this->set('datum', date("d.m.Y", $this->starttime));
		$this->set('zeit', date("H:i", $this->starttime));
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!isset($this->XML->Lap))
			$this->addError('Die Trainingsdatei enth&auml;lt keine Runden.');
		else
			foreach ($this->XML->Lap as $Lap)
				$this->parseLap($Lap);
	}

	/**
	 * Parse one single lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLap($Lap) {
		$this->parseLapValues($Lap);
		$this->parseTrackpoints($Lap);
	}

	/**
	 * Parse general lap-values
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLapValues($Lap) {
		if (!empty($Lap->Calories))
			$this->calories += (int)$Lap->Calories;

		$this->data['laps_distance'] += round((int)$Lap->DistanceMeters)/1000;
		$this->data['laps_time']     += round((int)$Lap->TotalTimeSeconds);

		// TODO: save pause-laps too with special identification
		if ((string)$Lap->Intensity == 'Active')
			$this->data['splits'][] = round((int)$Lap->DistanceMeters/1000, 2).'|'.Helper::Time(round((int)$Lap->TotalTimeSeconds), false, 2);
	}

	/**
	 * Parse all trackpoints for one lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseTrackpoints($Lap) {
		$this->lastPoint = 0;

		foreach ($Lap->Track as $Track)
			foreach ($Track->Trackpoint as $Trackpoint)
				$this->parseTrackpoint($Trackpoint);
	}

	/**
	 * Parse one trackpoint
	 * @param SimpleXMLElement $TP
	 */
	protected function parseTrackpoint($TP) {
		if (empty($TP->DistanceMeters)) {
			$this->lastPointWasEmpty = true;
			return;
		}

		if ($this->lastPointWasEmpty) {
			$this->starttime = strtotime((string)$TP->Time) - end($this->data['time_in_s']);
		}

		$this->lastPointWasEmpty   = false;
		$this->lastPoint           = (int)$TP->DistanceMeters;
		$this->data['time_in_s'][] = strtotime((string)$TP->Time) - $this->starttime;
		$this->data['km'][]  = round((int)$TP->DistanceMeters)/1000;
		$this->data['altitude'][]  = (int)$TP->AltitudeMeters;
		$this->data['pace'][]      = ((end($this->data['km']) - prev($this->data['km'])) != 0)
									? round((end($this->data['time_in_s']) - prev($this->data['time_in_s'])) / (end($this->data['km']) - prev($this->data['km'])))
									: 0;
		$this->data['heartrate'][] = (!empty($TP->HeartRateBpm))
									? round($TP->HeartRateBpm->Value)
									: 0;

		if (!empty($TP->Position)) {
			$this->data['latitude'][]  = (double)$TP->Position->LatitudeDegrees;
			$this->data['longitude'][] = (double)$TP->Position->LongitudeDegrees;
		} elseif (!empty($this->data['latitude'])) {
			$this->data['latitude'][]  = end($this->data['latitude']);
			$this->data['longitude'][] = end($this->data['longitude']);
		} else {
			$this->data['latitude'][]  = 0;
			$this->data['longitude'][] = 0;
		}
	}

	/**
	 * Try to get current sport id
	 * @return string 
	 */
	protected function getCurrentSportId() {
		if (!is_null($this->XML) && isset($this->XML->attributes()->Sport)) {
			$Name = $this->XML->attributes()->Sport;
			$Id   = Sport::getIdByName($Name);

			if ($Id > 0)
				return $Id;
			else {
				if ($Name == 'Running')
					$Name = 'Laufen';
				if ($Name == 'Biking')
					$Name = 'Radfahren';
				if ($Name == 'Swimming')
					$Name = 'Schwimmen';
				if ($Name == 'Other')
					$Name = 'Sonstiges';

				$Id = Sport::getIdByName($Name);

				if ($Id > 0)
					return $Id;
			}
		}

		return CONF_RUNNINGSPORT;
	}
}