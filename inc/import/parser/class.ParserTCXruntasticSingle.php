<?php
/**
 * This file contains class::ParserTCXruntasticSingle
 * @package Runalyze\Import\Parser
 * Pause detection is not working with data by runtastic, because of their crap data
 */

/**
 * Parser for TCX files from Runtastic (Garminfiletype)
 *
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Import\Parser
 */
class ParserTCXruntasticSingle extends ParserTCXSingle {
	/**
	 * @var int
	 */
	protected $CurrentIndex = 0;

	/**
	 * @var int
	 */
	protected $LastActiveIndex = 0;
	
	/**
	 * @var int
	 */
	protected $LastValidHR = 0;

	/**
	 * Add error: no garmin file
	 */
	protected function throwNoGarminError() {
		$this->addError( __('Given XML object is not from Runtastic. &lt;Id&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		parent::parseGeneralValues();

		$this->TrainingObject->setTimeInSeconds(((string)$this->XML->Lap->TotalTimeSeconds));
	}

	/**
	 * Parse one trackpoint
	 * @param SimpleXMLElement $TP
	 */
	protected function parseTrackpoint(&$TP) {
		if ($this->distancesAreEmpty)
			$TP->addChild('DistanceMeters', 1000*$this->distanceToTrackpoint($TP));

		if (!empty($TP->HeartRateBpm)) {
			$this->LastValidHR = round($TP->HeartRateBpm->Value);
		}
		$this->gps['time_in_s'][]  = strtotime((string)$TP->Time) - $this->TrainingObject->getTimestamp();
		$this->gps['km'][]         = round((float)$TP->DistanceMeters/1000, ParserAbstract::DISTANCE_PRECISION);
		$this->gps['altitude'][]   = (int)$TP->AltitudeMeters;
		$this->gps['heartrate'][]  = $this->LastValidHR;

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

		$this->CurrentIndex++;

		$this->parseExtensionValues($TP);
	}

	/**
	 * Get current pace
	 * @return int
	 */
	protected function getCurrentPaceForRuntastic() {
		$currDist = $this->gps['km'][$this->CurrentIndex];
		$lastDist = $this->gps['km'][$this->LastActiveIndex];
		$currTime = $this->gps['time_in_s'][$this->CurrentIndex];
		$lastTime = $this->gps['time_in_s'][$this->LastActiveIndex];

		if ($currDist > $lastDist) {
			return round( ($currTime - $lastTime) / ($currDist - $lastDist) );
		}

		return 0;
	}
}