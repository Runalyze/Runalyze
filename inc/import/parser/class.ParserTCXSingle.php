<?php
/**
 * This file contains class::ParserTCXSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Distance;

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
		$this->addError( __('Given XML object is not from Garmin. &lt;Id&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->Id) );
		$this->TrainingObject->setActivityId( (string)$this->XML->Id );
		$this->TrainingObject->setCreatorDetails( $this->findCreator() );
		$this->findSportId();

		if (!empty($this->XML->Training))
			$this->TrainingObject->setComment( (string)$this->XML->Training->Plan->Name );
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!isset($this->XML->Lap)) {
			$this->addError( __('This file does not contain any laps.') );
		} else {
			foreach ($this->XML->Lap as $i => $Lap) {
				if ($i == 0) {
					if (isset($Lap['StartTime'])) {
						$start = strtotime((string)$Lap['StartTime']);
						if ($start < $this->TrainingObject->getTimestamp()) {
							$this->TrainingObject->setTimestamp($start);
						}
					}

					if (!empty($Lap->Track) && !empty($Lap->Track[0]->Trackpoint[0])) {
						$start = strtotime((string)$Lap->Track[0]->Trackpoint[0]->Time);
						if ($start < $this->TrainingObject->getTimestamp()) {
							$this->TrainingObject->setTimestamp($start);
						}
					}
				}

				$this->parseLap($Lap);
			}
		}

		if (!empty($this->gps['time_in_s']))
			$this->TrainingObject->setElapsedTime( end($this->gps['time_in_s']) + $this->PauseInSeconds );
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
			Error::getInstance()->addDebug('LAPS-TIME: '.Duration::format(round((float)$Lap->TotalTimeSeconds)));

		if ((int)$Lap->DistanceMeters == 0 && (int)$Lap->TotalTimeSeconds > 10)
			$this->isWithoutDistance = true;
		elseif (isset($Lap->Track[0]) && count($Lap->Track[0]->Trackpoint) > 5
				&& (double)$Lap->Track[0]->Trackpoint[0]->DistanceMeters == (double)$Lap->Track[0]->Trackpoint[count($Lap->Track[0]->Trackpoint)-2]->DistanceMeters)
			$this->isWithoutDistance = true;
		else
			$this->isWithoutDistance = false;
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

		if (self::$DEBUG_SPLITS) {
			Error::getInstance()->addDebug( 'computed: '.Duration::format( end($this->gps['time_in_s']) ).', '.Distance::format(end($this->gps['km'])) );
		}
	}

	/**
	 * Parse one trackpoint
	 * @param SimpleXMLElement $TP
	 */
	protected function parseTrackpoint(&$TP) {
		if ($this->distancesAreEmpty)
			$TP->addChild('DistanceMeters', 1000*$this->distanceToTrackpoint($TP));
		//else if ((float)$TP->DistanceMeters < $this->gps['km'])
		//	$TP->DistanceMeters = 1000*$this->distanceToTrackpoint($TP);

		$ThisBreakInMeter   = (float)$TP->DistanceMeters - $this->lastDistance;
		$ThisBreakInSeconds = (strtotime((string)$TP->Time) - $this->TrainingObject->getTimestamp() - end($this->gps['time_in_s'])) - $this->PauseInSeconds;

		if (Configuration::ActivityForm()->detectPauses()) {
			$NoMove = ($this->lastDistance == (float)$TP->DistanceMeters) && !$this->isWithoutDistance;
			$TooSlow = !$this->lastPointWasEmpty && $ThisBreakInMeter > 0 && ($ThisBreakInSeconds / $ThisBreakInMeter > 6);
		} else {
			$NoMove=$TooSlow=false;
		}

		if ((empty($TP->DistanceMeters) && !$this->isWithoutDistance ) || $NoMove || $TooSlow) {
			$Ignored = false;

			if (count($TP->children()) == 1 || $NoMove || $TooSlow) {
				if ($NoMove && $ThisBreakInSeconds <= self::$IGNORE_NO_MOVE_UNTIL)
					$Ignored = true;
				else
					$this->PauseInSeconds += $ThisBreakInSeconds;
				if (self::$DEBUG_SPLITS)
					Error::getInstance()->addDebug('PAUSE at '.(string)$TP->Time.' of '.$ThisBreakInSeconds.', empty point: '.
							($NoMove ?
								'no move'.($Ignored ? ' ignored' : '')
								: 'empty trackpoint').($TooSlow ? ' (too slow, '.$ThisBreakInMeter.'m in '.$ThisBreakInSeconds.'s)' : ''));
			}

			if (!$Ignored)
				return;
		}

		if (empty($TP->DistanceMeters) && !empty($this->gps['km']))
			$TP->DistanceMeters = end($this->gps['km'])*1000;

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
		$this->gps['km'][]         = round((float)$TP->DistanceMeters/1000, ParserAbstract::DISTANCE_PRECISION);
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

		$this->parseExtensionValues($TP);
	}

	/**
	 * Parse extension values
	 * @param SimpleXMLElement $Point
	 * @return int
	 */
	private function parseExtensionValues(SimpleXMLElement &$Point) {
		$power = 0;
		$rpm   = 0;

		if (!empty($Point->Cadence))
			$rpm = (int)$Point->Cadence;

		if (isset($Point->Extensions)) {
			if (isset($Point->Extensions->TPX) && isset($Point->Extensions->TPX->RunCadence))
				$rpm = (int)$Point->Extensions->TPX->RunCadence;

			if (isset($Point->Extensions->TPX) && isset($Point->Extensions->TPX->Watts))
				$power = (int)$Point->Extensions->TPX->Watts;

			if (count($Point->Extensions->children('ns3',true)) > 0) {
				if (isset($Point->Extensions->children('ns3',true)->TPX)) {
					$TPX = $Point->Extensions->children('ns3',true)->TPX;

					if (count($TPX->children('ns3',true)) > 0 && isset($TPX->children('ns3',true)->Watts))
						$power = (int)$TPX->children('ns3',true)->Watts;
				}
			}
		}

		$this->gps['power'][] = $power;
		$this->gps['rpm'][]   = $rpm;
	}

	/**
	 * Calculate distance to trackpoint
	 * @param SimpleXMLElement $TP
	 * @return int
	 */
	private function distanceToTrackpoint(SimpleXMLElement &$TP) {
		if (empty($this->gps['km']))
			return empty($TP->Position) ? 0 : 0.001;

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
		if (!is_null($this->XML) && isset($this->XML->attributes()->Sport))
			$this->guessSportID((string)$this->XML->attributes()->Sport, $this->findCreator());
		else
			$this->TrainingObject->setSportid( Configuration::General()->runningSport() );
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
