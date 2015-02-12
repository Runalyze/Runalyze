<?php
/**
 * This file contains class::ParserFITLOGSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Parser for FITLOG files from SportTracks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserFITLOGSingle extends ParserAbstractSingleXML {
	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectFITLOG()) {
			$this->parseGeneralValues();
			$this->parseLaps();
			$this->parseTrack();
			$this->setGPSarrays();
		} else {
			$this->throwNoFITLOGError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectFITLOG() {
		return property_exists($this->XML, 'Duration');
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoFITLOGError() {
		$this->addError( __('Given XML object is not from SportTracks. &lt;Duration&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML['StartTime']) );
		$this->TrainingObject->setSportid( Configuration::General()->mainSport() );

		if (!empty($this->XML->Duration['TotalSeconds']))
			$this->TrainingObject->setTimeInSeconds(round((double)$this->XML->Duration['TotalSeconds']));

		if (!empty($this->XML->Distance['TotalMeters']))
			$this->TrainingObject->setDistance(round((double)$this->XML->Distance['TotalMeters'])/1000);

		if (!empty($this->XML->Calories['TotalCal']))
			$this->TrainingObject->setCalories((int)$this->XML->Calories['TotalCal']);

		if (!empty($this->XML->Location['Name']))
			$this->TrainingObject->setRoute((string)$this->XML->Location['Name']);

		if (!empty($this->XML->Weather['Temp']))
			$this->TrainingObject->setTemperature((int)$this->XML->Weather['Temp']);

		if (!empty($this->XML->HeartRateMaximumBPM))
			$this->TrainingObject->setPulseMax((int)$this->XML->HeartRateMaximumBPM);

		if (!empty($this->XML->HeartRateAverageBPM))
			$this->TrainingObject->setPulseAvg((int)$this->XML->HeartRateAverageBPM);
	}

	/**
	 * Parse all log entries
	 */
	protected function parseTrack() {
		if (isset($this->XML->Track->pt))
			foreach ($this->XML->Track->pt as $Point)
				$this->parseTrackpoint($Point);
	}

	/**
	 * Parse trackpoint
	 * @param SimpleXMLElement $Point 
	 */
	protected function parseTrackpoint($Point) {
		if (!empty($Point['lat'])) {
			$lat  = round((double)$Point['lat'], 7);
			$lon  = round((double)$Point['lon'], 7);
			$dist = empty($this->gps['latitude'])
					? 0
					: round(GpsData::distance($lat, $lon, end($this->gps['latitude']), end($this->gps['longitude'])), ParserAbstract::DISTANCE_PRECISION);
		} elseif (count($this->gps['latitude'])) {
			$lat  = end($this->gps['latitude']);
			$lon  = end($this->gps['longitude']);
			$dist = 0;
		} else
			return;

		$this->gps['time_in_s'][] = (int)$Point['tm'];
		$this->gps['km'][]        = empty($this->gps['km']) ? $dist : $dist + end($this->gps['km']);
		$this->gps['pace'][]      = $this->getCurrentPace();
		$this->gps['latitude'][]  = $lat;
		$this->gps['longitude'][] = $lon;
		$this->gps['altitude'][]  = (!empty($Point['ele'])) ? (int)$Point['ele'] : 0;
		$this->gps['heartrate'][] = (!empty($Point['hr'])) ? (int)$Point['hr'] : 0;
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!isset($this->XML->Laps))
			return;

		$Distance = 0;
		$Calories = 0;
		foreach ($this->XML->Laps->children() as $Lap) {
			$LapDist = (!empty($Lap->Distance['TotalMeters'])) ? ((int)$Lap->Distance['TotalMeters'])/1000 : 0;
			$Distance += $LapDist;

			if (!empty($Lap['DurationSeconds'])) {
				$this->TrainingObject->Splits()->addSplit($LapDist, (int)$Lap['DurationSeconds']);
			}

			if (!empty($Lap->Distance['TotalCal']))
				$Calories += (int)$Lap->Calories['TotalCal'];
		}

		if ($Distance > 0)
			$this->TrainingObject->setDistance($Distance);
		if ($Calories > 0)
			$this->TrainingObject->setCalories($Calories);
	}
}