<?php
/**
 * This file contains class::ParserTCXSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Activity\Duration;
use Runalyze\Activity\Distance;
use Runalyze\Configuration;
use Runalyze\Error;
use Runalyze\Import\Exception\UnsupportedFileException;

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
	private static $DEBUG_SPLITS = false;

	/**
	 * Ignore "empty" moves until this number of seconds
	 * @var int number of seconds
	 */
	private static $IGNORE_NO_MOVE_UNTIL = 1;

	/**
	 * Total pause time
	 * @var int
	 */
	protected $PauseInSeconds = 0;

	/**
	 * Last point
	 * @var int
	 */
	protected $lastPoint = 0;

	/**
	 * Last distance (exact)
	 * @var float
	 */
	protected $lastDistance = -1;

	/**
	 * Boolean flag: Last point was empty
	 * @var boolean
	 */
	protected $lastPointWasEmpty = false;

	/**
	 * Boolean flag: without distance (indoor training)
	 * @var boolean
	 */
	protected $isWithoutDistance = false;

	/**
	 * Boolean flag: distances are empty
	 * @var boolean
	 */
	protected $distancesAreEmpty = false;

	/**
	 * @var bool
	 */
	protected $wasPause = false;

	/**
	 * @var int
	 */
	protected $pauseDuration = 0;

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
			$this->correctCadenceIfNeeded();
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
	 * @throws \Runalyze\Import\Exception\UnsupportedFileException
	 */
	protected function throwNoGarminError() {
		throw new UnsupportedFileException('Given XML object is not from Garmin. &lt;Id&gt;-tag could not be located.');
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->XML->Id);

		$this->TrainingObject->setCreatorDetails( $this->findCreator() );
		$this->findSportId();

		if (!empty($this->XML->Notes))
            $this->TrainingObject->setNotes( (string)$this->XML->Notes );

		if (!empty($this->XML->Training))
			$this->TrainingObject->setTitle( (string)$this->XML->Training->Plan->Name );
	}

	/**
	 * Parse all laps
	 * @throws \Runalyze\Import\Exception\UnsupportedFileException
	 */
	protected function parseLaps() {
		if (!isset($this->XML->Lap)) {
			throw new UnsupportedFileException('This file does not contain any laps.');
		} else {
			foreach ($this->XML->Lap as $i => $Lap) {
				if ($i == 0) {
					if (isset($Lap['StartTime'])) {
						$start = $this->strtotime((string)$Lap['StartTime']);
						if ($start < $this->TrainingObject->getTimestamp()) {
							$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$Lap['StartTime']);
						}
					}

					if (!empty($Lap->Track) && !empty($Lap->Track[0]->Trackpoint[0])) {
						$start = $this->strtotime((string)$Lap->Track[0]->Trackpoint[0]->Time);
						if ($start < $this->TrainingObject->getTimestamp()) {
							$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$Lap->Track[0]->Trackpoint[0]->Time);
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

				if ($this->strtotime((string)$Lap['StartTime']) + 8 < $this->strtotime((string)$Track->Trackpoint[0]->Time))
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

		$ThisBreakInMeter   = (float)$TP->DistanceMeters - $this->lastDistance;
		$ThisBreakInSeconds = ($this->strtotime((string)$TP->Time) - $this->TrainingObject->getTimestamp() - end($this->gps['time_in_s'])) - $this->PauseInSeconds;

		if ($ThisBreakInSeconds <= 0) {
			return;
		}

		if (Configuration::ActivityForm()->detectPauses() && !$this->isWithoutDistance) {
			$NoMove = ($this->lastDistance == (float)$TP->DistanceMeters);
			$TooSlow = !$this->lastPointWasEmpty && $ThisBreakInMeter > 0 && ($ThisBreakInSeconds / $ThisBreakInMeter > 6);
		} else {
			$NoMove=$TooSlow=false;
		}

		if ((empty($TP->DistanceMeters) && !$this->isWithoutDistance ) || $NoMove || $TooSlow) {
			$Ignored = false;

			if (count($TP->children()) == 1 || $NoMove || $TooSlow) {
				if ($NoMove && $ThisBreakInSeconds <= self::$IGNORE_NO_MOVE_UNTIL) {
					$Ignored = true;
				} else {
					$this->PauseInSeconds += $ThisBreakInSeconds;
					$this->wasPause = true;
					$this->pauseDuration += $ThisBreakInSeconds;
				}

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
			$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$TP->Time);

		if ($this->lastPointWasEmpty) {
			$OldPauseInSeconds = $this->PauseInSeconds;
			$this->PauseInSeconds = ($this->strtotime((string)$TP->Time) - $this->TrainingObject->getTimestamp() - end($this->gps['time_in_s']));
			$this->pauseDuration += $this->PauseInSeconds - $OldPauseInSeconds;
			$this->wasPause = true;

			if (self::$DEBUG_SPLITS)
				Error::getInstance()->addDebug('PAUSE at '.(string)$TP->Time.' of '.($this->PauseInSeconds - $OldPauseInSeconds).
						', last point was empty');
		}

		if ($this->wasPause) {
			$this->TrainingObject->Pauses()->add(
				new \Runalyze\Model\Trackdata\Pause(
					end($this->gps['time_in_s']),
					$this->pauseDuration,
					end($this->gps['heartrate']),
					(!empty($TP->HeartRateBpm)) ? round($TP->HeartRateBpm->Value) : 0
				)
			);

			$this->wasPause = false;
			$this->pauseDuration = 0;
		}

		$this->lastPointWasEmpty   = false;
		$this->lastPoint           = (int)$TP->DistanceMeters;
		$this->lastDistance        = (float)$TP->DistanceMeters;
		$this->gps['time_in_s'][]  = $this->strtotime((string)$TP->Time) - $this->TrainingObject->getTimestamp() - $this->PauseInSeconds;
		$this->gps['km'][]         = round((float)$TP->DistanceMeters/1000, ParserAbstract::DISTANCE_PRECISION);
		$this->gps['altitude'][]   = (int)$TP->AltitudeMeters;
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
	protected function parseExtensionValues(SimpleXMLElement $Point) {
		$power = 0;
		$rpm   = 0;

		if (!empty($Point->Cadence))
			$rpm = (int)$Point->Cadence;

		if (isset($Point->Extensions)) {
			if (isset($Point->Extensions->TPX) && isset($Point->Extensions->TPX->RunCadence))
				$rpm = (int)$Point->Extensions->TPX->RunCadence;

			if (isset($Point->Extensions->TPX) && isset($Point->Extensions->TPX->Watts))
				$power = (int)$Point->Extensions->TPX->Watts;

			$this->parsePowerFromExtensionValues($Point->Extensions, 'ns2', $power, $rpm);
			$this->parsePowerFromExtensionValues($Point->Extensions, 'ns3', $power, $rpm);
		}

		$this->gps['power'][] = $power;
		$this->gps['rpm'][]   = $rpm;
	}
	
	protected function parsePowerFromExtensionValues(SimpleXMLElement $Extensions, $namespace, &$power, &$rpm) {
		if (count($Extensions->children($namespace,true)) > 0) {

			if (isset($Extensions->children($namespace,true)->TPX)) {
				$TPX = $Extensions->children($namespace,true)->TPX;

				if (count($TPX->children($namespace,true)) > 0 && isset($TPX->children($namespace,true)->Watts))
					$power = (int)$TPX->children($namespace,true)->Watts;
                if (count($TPX->children($namespace,true)) > 0 && isset($TPX->children($namespace,true)->RunCadence))
					$rpm = (int)$TPX->children($namespace,true)->RunCadence;
			}
		}
	}

	/**
	 * Calculate distance to trackpoint
	 * @param SimpleXMLElement $TP
	 * @return int
	 */
	protected function distanceToTrackpoint(SimpleXMLElement $TP) {
		if (empty($this->gps['km']))
			return empty($TP->Position) ? 0 : 0.001;

		if (empty($TP->Position))
			return end($this->gps['km']);

		if (end($this->gps['latitude']) == 0 && end($this->gps['longitude']) == 0)
			return end($this->gps['km']) + 0.001;

		return end($this->gps['km']) +
			Runalyze\Model\Route\Entity::gpsDistance(
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
