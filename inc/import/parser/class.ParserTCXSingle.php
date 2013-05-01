<?php
/**
 * This file contains class::ParserTCXSingle
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for TCX files from Garmin
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserTCXSingle extends ParserAbstractSingleXML {
	/**
	 * Debug splits
	 * @var boolean
	 */
	static private $DEBUG_SPLITS = false;

	/**
	 * Ignore "empty" moves until this number of seconds
	 * @var int number of seconds
	 */
	static private $IGNORE_NO_MOVE_UNTIL = 1;

	/**
	 * Total pause time
	 * @var int
	 */
	protected $PauseInSeconds = 0;

	/**
	 * Last point
	 * @var int
	 */
	private $lastPoint = 0;

	/**
	 * Last distance (exact)
	 * @var float
	 */
	private $lastDistance = -1;

	/**
	 * Boolean flag: Last point was empty
	 * @var boolean
	 */
	private $lastPointWasEmpty = false;

	/**
	 * Boolean flag: without distance (indoor training)
	 * @var boolean
	 */
	private $isWithoutDistance = false;

	/**
	 * Boolean flag: distances are empty
	 * @var boolean
	 */
	private $distancesAreEmpty = false;

	/**
	 * Constructor
	 * 
	 * This parser reimplements constructor to force $XML-parameter to be given.
	 * 
	 * @param string $FileContent file content
	 * @param SimpleXMLElement $XML XML element with <Activity> as root tag
	 */
	public function __construct($FileContent, SimpleXMLElement $XML) {
		parent::__construct($FileContent, $XML);
	}

	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isGarminXML()) {
			$this->parseGeneralValues();
			$this->parseLaps();
			$this->setGPSarrays();
		} else {
			$this->throwNoGarminError();
		}
	}

	/**
	 * Is given XML from garmin?
	 * @return bool
	 */
	protected function isGarminXML() {
		return isset($this->XML->Id);
	}

	/**
	 * Add error: no garmin file
	 */
	protected function throwNoGarminError() {
		$this->addError('Given XML object is not from Garmin. <Id>-tag could not be located.');
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->Id) );
		$this->TrainingObject->setSportid( $this->findSportId() );
		$this->TrainingObject->setActivityId( (string)$this->XML->Id );
		$this->TrainingObject->setCreatorDetails( $this->findCreator() );

		if (!empty($this->XML->Training))
			$this->TrainingObject->setComment( (string)$this->XML->Training->Plan->Name );
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!isset($this->XML->Lap)) {
			$this->addError('Die Trainingsdatei enth&auml;lt keine Runden.');
		} else {
			foreach ($this->XML->Lap as $Lap)
				$this->parseLap($Lap);
		}
	}

	/**
	 * Parse lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLap(&$Lap) {
		$this->parseLapValues($Lap);
		$this->parseTrackpoints($Lap);
	}

	/**
	 * Parse general lap-values
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLapValues(&$Lap) {
		if (!empty($Lap->Calories))
			$this->TrainingObject->addCalories((int)$Lap->Calories);

		$this->TrainingObject->Splits()->addSplit(
			round((int)$Lap->DistanceMeters)/1000,
			round((float)$Lap->TotalTimeSeconds),
			((string)$Lap->Intensity == 'Active')
		);

		if (self::$DEBUG_SPLITS)
			Error::getInstance()->addDebug('LAPS-TIME: '.Time::toString(round((float)$Lap->TotalTimeSeconds), false, 2));

		if ((int)$Lap->DistanceMeters == 0 && (int)$Lap->TotalTimeSeconds > 10)
			$this->isWithoutDistance = true;
	}

	/**
	 * Parse all trackpoints for one lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseTrackpoints(&$Lap) {
		$this->lastPoint = 0;

		foreach ($Lap->Track as $Track) {
			if ($this->lastPoint > 0)
				$this->lastPointWasEmpty = true;

			if (count($Track->xpath('./Trackpoint')) > 0) {
				$this->distancesAreEmpty = (count($Track->xpath('./Trackpoint/DistanceMeters')) == 0);

				if (strtotime((string)$Lap['StartTime']) + 8 < strtotime((string)$Track->Trackpoint[0]->Time))
					$this->lastPointWasEmpty = true;

				foreach ($Track->Trackpoint as $Trackpoint)
					$this->parseTrackpoint($Trackpoint);
			}
		}

		if (self::$DEBUG_SPLITS)
			Error::getInstance()->addDebug( Time::toString( end($this->gps['time_in_s']) ) );
	}

	/**
	 * Parse one trackpoint
	 * @param SimpleXMLElement $TP
	 */
	protected function parseTrackpoint(&$TP) {
		if ($this->distancesAreEmpty)
			$TP->addChild('DistanceMeters', 1000*$this->distanceToTrackpoint($TP));

		$NoMove = ($this->lastDistance == (float)$TP->DistanceMeters) && !$this->isWithoutDistance;

		if (empty($TP->DistanceMeters) || $NoMove) {
			$Ignored = false;

			if (count($TP->children()) == 1 || $NoMove) {
				$ThisBreakInSeconds = (strtotime((string)$TP->Time) - $this->TrainingObject->getTimestamp() - end($this->gps['time_in_s'])) - $this->PauseInSeconds;

				if ($NoMove && $ThisBreakInSeconds <= self::$IGNORE_NO_MOVE_UNTIL) 
					$Ignored = true;
				else
					$this->PauseInSeconds += $ThisBreakInSeconds;
				if (self::$DEBUG_SPLITS)
					Error::getInstance()->addDebug('PAUSE at '.(string)$TP->Time.' of '.$ThisBreakInSeconds.', empty point: '.
							($NoMove ?
								'no move'.($Ignored ? ' ignored' : '')
								: 'empty trackpoint'));
			}

			if (!$Ignored)
				return;
		}

		if ($this->TrainingObject->getTimestamp() == 0)
			$this->TrainingObject->setTimestamp( strtotime((string)$TP->Time) );

		if ($this->lastPointWasEmpty) {
			$OldPauseInSeconds = $this->PauseInSeconds;
			$this->PauseInSeconds = (strtotime((string)$TP->Time) - $this->TrainingObject->getTimestamp() - end($this->gps['time_in_s']));

			if (self::$DEBUG_SPLITS)
				Error::getInstance()->addDebug('PAUSE at '.(string)$TP->Time.' of '.($this->PauseInSeconds - $OldPauseInSeconds).
						', last point was empty');
		}

		$this->lastPointWasEmpty   = false;
		$this->lastPoint           = (int)$TP->DistanceMeters;
		$this->lastDistance        = (float)$TP->DistanceMeters;
		$this->gps['time_in_s'][]  = strtotime((string)$TP->Time) - $this->TrainingObject->getTimestamp() - $this->PauseInSeconds;
		$this->gps['km'][]         = round((int)$TP->DistanceMeters)/1000;
		$this->gps['altitude'][]   = (int)$TP->AltitudeMeters;
		$this->gps['pace'][]       = $this->getCurrentPace();
		$this->gps['heartrate'][]  = (!empty($TP->HeartRateBpm))
									? round($TP->HeartRateBpm->Value)
									: 0;

		if (!empty($TP->Position)) {
			$this->gps['latitude'][]  = (double)$TP->Position->LatitudeDegrees;
			$this->gps['longitude'][] = (double)$TP->Position->LongitudeDegrees;
		} elseif (!empty($this->gps['latitude'])) {
			$this->gps['latitude'][]  = end($this->gps['latitude']);
			$this->gps['longitude'][] = end($this->gps['longitude']);
		} else {
			$this->gps['latitude'][]  = 0;
			$this->gps['longitude'][] = 0;
		}
	}

	/**
	 * Calculate distance to trackpoint
	 * @param SimpleXMLElement $TP
	 * @return int
	 */
	private function distanceToTrackpoint(SimpleXMLElement &$TP) {
		if (empty($this->gps['km']))
			return 0.001;

		if (empty($TP->Position))
			return end($this->gps['km']);

		return end($this->gps['km']) +
			GpsData::distance(
				end($this->gps['latitude']), end($this->gps['longitude']),
				(double)$TP->Position->LatitudeDegrees, (double)$TP->Position->LongitudeDegrees
			);
	}

	/**
	 * Try to get current sport id
	 * @return int 
	 */
	protected function findSportId() {
		if (!is_null($this->XML) && isset($this->XML->attributes()->Sport)) {
			$Name = $this->XML->attributes()->Sport;
			$Id   = SportFactory::idByName($Name);

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

				$Id = SportFactory::idByName($Name);

				if ($Id > 0)
					return $Id;
			}
		}

		return CONF_RUNNINGSPORT;
	}

	/**
	 * Get name of creator
	 * @return string
	 */
	protected function findCreator() {
		if (isset($this->XML->Creator))
			if (isset($this->XML->Creator->Name))
				return (string)$this->XML->Creator->Name;

		return '';
	}
}