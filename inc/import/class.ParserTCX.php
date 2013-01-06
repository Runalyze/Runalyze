<?php
/**
 * Parser for tcx-files from Garmin
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class ParserTCX extends Parser {
	/**
	 * Debug splits
	 * @var boolean
	 */
	static public $DEBUG_SPLITS = false;

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
	 * Number of trainings (first sport, for multisport sessions)
	 * @var int
	 */
	private $numberOfTrainingsFirstSport = 0;

	/**
	 * Number of trainings (next sport, for multisport sessions)
	 * @var int
	 */
	private $numberOfTrainingsNextSport = 0;

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
	 * Boolean flag: loading xml failed
	 * @var boolean
	 */
	private $loadXmlFailed = false;

	/**
	 * Construct a new parser, needs XML
	 * @param SimpleXMLElement $XML
	 */
	public function __construct($XML) {
		$this->CompleteXML = simplexml_load_string_utf8($XML);

		if ($this->CompleteXML == false) {
			Filesystem::throwErrorForBadXml($XML);
			$this->loadXmlFailed = true;
			return false;
		}

		if ($this->checkXML())
			$this->initXML();
	}

	/**
	 * Parse current training 
	 */
	public function parseTraining() {
		$this->initEmptyValues();

		if ($this->loadXmlFailed) {
			$this->addError('Die XML-Datei konnte nicht erfolgreich geladen werden.');
			return;
		}

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
		return $this->numberOfTrainings() > 1;
	}

	/**
	 * Get number of trainings
	 * @return int
	 */
	public function numberOfTrainings() {
		return $this->numberOfTrainings + $this->numberOfTrainingsFirstSport + $this->numberOfTrainingsNextSport;
	}

	/**
	 * Init internal XML
	 */
	protected function initXML() {
		if (isset($this->CompleteXML->Activities->MultiSportSession)) {
			if (isset($this->CompleteXML->Activities->MultiSportSession->FirstSport))
				$this->numberOfTrainingsFirstSport = count($this->CompleteXML->Activities->MultiSportSession->FirstSport->Activity);
			if (isset($this->CompleteXML->Activities->MultiSportSession->NextSport))
				$this->numberOfTrainingsNextSport  = count($this->CompleteXML->Activities->MultiSportSession->NextSport->Activity);
		}

		$this->currentTraining   = 0;
		$this->numberOfTrainings = count($this->CompleteXML->Activities->Activity);

		$this->setXMLfromIndex($this->currentTraining);
	}

	/**
	 * Set internal XML from index of training
	 * @param int $index 
	 */
	protected function setXMLfromIndex($index) {
		if ($index < $this->numberOfTrainings)
			$this->XML = $this->CompleteXML->Activities->Activity[$index];
		else {
			$index -= $this->numberOfTrainings;

			if ($index < $this->numberOfTrainingsFirstSport)
				$this->XML = $this->CompleteXML->Activities->MultiSportSession->FirstSport->Activity[$index];
			else {
				$index -= $this->numberOfTrainingsFirstSport;

				if ($index < $this->numberOfTrainingsNextSport)
					$this->XML = $this->CompleteXML->Activities->MultiSportSession->NextSport->Activity[$index];
			}
		}
	}

	/**
	 * Go to next training if available
	 * @return boolean
	 */
	public function nextTraining() {
		if ($this->currentTraining < $this->numberOfTrainings()) {
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
			'splits'        => array(),
			'splits_resting'=> array());
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
		$this->setCreatorValues();
		$this->set('activity_id', (string)$this->XML->Id);
		$this->set('sportid', $this->getCurrentSportId());
		$this->set('kcal', $this->calories);

		if (empty($this->data['splits']))
			$this->data['splits'] = $this->data['splits_resting'];

		$this->set('splits', implode('-', $this->data['splits']));
		$this->set('use_vdot', 1);
	}

	/**
	 * Set values about creator 
	 */
	protected function setCreatorValues() {
		$this->set('creator_details', 'ID: '.trim($this->getCreator()));
	}

	/**
	 * Get name of creator
	 * @return string
	 */
	protected function getCreator() {
		if (isset($this->XML->Creator))
			if (isset($this->XML->Creator->Name))
				return (string)$this->XML->Creator->Name;
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
		if (!isset($this->XML->Lap)) {
			$this->addError('Die Trainingsdatei enth&auml;lt keine Runden.');
		} else
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
		$this->data['laps_time']     += round((float)$Lap->TotalTimeSeconds);

		// TODO: save pause-laps too with special identification
		$SplitString = round((int)$Lap->DistanceMeters/1000, 2).'|'.Time::toString(round((float)$Lap->TotalTimeSeconds), false, 2);
		$SplitKey    = ((string)$Lap->Intensity == 'Active') ? 'splits' : 'splits_resting';
		$this->data[$SplitKey][] = $SplitString;

		if (self::$DEBUG_SPLITS)
			echo 'LAPS-TIME: '.Time::toString(round((float)$Lap->TotalTimeSeconds), false, 2).'<br />';
	}

	/**
	 * Parse all trackpoints for one lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseTrackpoints($Lap) {
		$this->lastPoint = 0;

		foreach ($Lap->Track as $Track) {
			if ($this->lastPoint > 0)
				$this->lastPointWasEmpty = true;
			if (strtotime((string)$Lap['StartTime']) + 8 < strtotime((string)$Track->Trackpoint[0]->Time))
				$this->lastPointWasEmpty = true;

			foreach ($Track->Trackpoint as $Trackpoint)
				$this->parseTrackpoint($Trackpoint);
		}

		if (self::$DEBUG_SPLITS)
			echo Time::toString(end($this->data['time_in_s'])).'<br />';
	}

	/**
	 * Parse one trackpoint
	 * @param SimpleXMLElement $TP
	 */
	protected function parseTrackpoint($TP) {
		// TODO: What the fuck?
		// - FR305: Pause when DistanceMeters is empty
		// -> should be only if trackpoint has ONLY time as child
		// - FR310XT: Pause -> new Track?
		if (empty($TP->DistanceMeters)) {
			if (count($TP->children()) == 1)
				$this->lastPointWasEmpty = true;

			return;
		}

		if ($this->lastPointWasEmpty) {
			if (self::$DEBUG_SPLITS)
				echo 'PAUSE at '.(string)$TP->Time.'<br />';

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