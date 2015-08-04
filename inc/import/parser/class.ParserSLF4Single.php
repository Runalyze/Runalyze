<?php
/**
 * This file contains class::ParserSLFSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Parser for SLF files from Sigma
 *
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Import\Parser
 */
class ParserSLF4Single extends ParserSLF3Single {
	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectSLF() {
		return !empty($this->XML->GeneralInformation);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoSLFError() {
		$this->addError( __('Given XML object is not from Sigma. &lt;LogEntries&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->GeneralInformation->startDate) );
		$this->TrainingObject->setCalories((int)$this->XML->GeneralInformation->calories);
		$this->TrainingObject->setSportid( Configuration::General()->mainSport() );
		$this->TrainingObject->setCreatorDetails( $this->findCreator() );
		$this->TrainingObject->setComment((string)$this->XML->GeneralInformation->name);
	}

	/**
	 * Parse all entries
	 */
	protected function parseLogEntries() {
		if (!isset($this->XML->Entries->Entry)) {
			if ($this->XML->GeneralInformation->trainingTime) {
				$this->TrainingObject->setTimeInSeconds((int)$this->XML->GeneralInformation->trainingTime/100);
				$this->TrainingObject->setDistance((float)$this->XML->GeneralInformation->distance/1000);
				$this->TrainingObject->setPulseMax((int)$this->XML->GeneralInformation->maximumHeartrate);
				$this->TrainingObject->setPulseAvg((int)$this->XML->GeneralInformation->averageHeartrate);
			} else {
			   $this->addError( __('This file does not contain any data.') );
			}
		} else {
			foreach ($this->XML->Entries->Entry as $Log)
				$this->parseEntry($Log);
		}

		if ($this->PauseInSeconds > 0 && !empty($this->gps['time_in_s']))
			$this->TrainingObject->setElapsedTime( $this->PauseInSeconds + end($this->gps['time_in_s']) );
	}

	/**
	 * Parse entry
	 * @param SimpleXMLElement $Log 
	 */
	protected function parseEntry($Log) {
		$Log = $Log->attributes();
		if ((int)$Log['trainingTime'] == 0) {
			return;
		}

		$this->gps['time_in_s'][] = (int)$Log['trainingTimeAbsolute']/100;
		$this->gps['km'][]        = round((float)$Log['distanceAbsolute']/1000, ParserAbstract::DISTANCE_PRECISION);
		$this->gps['heartrate'][] = (!empty($Log['heartrate']))
									? round($Log['heartrate'])
									: 0;
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!empty($this->XML->Markers))
			foreach ($this->XML->Markers->Marker as $Lap)
				$this->parseLap($Lap);
	}

	/**
	 * Parse one single lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLap($Lap) {
		$Lap = $Lap->attributes();
		if (!isset($Lap['calories']))
			$this->TrainingObject->addCalories( (int)$Lap['calories'] );

		$this->TrainingObject->Splits()->addSplit(
			round((int)$Lap['distance'])/1000,
			round((int)$Lap['time']/100),
			((string)$Lap['FastLap'] != 'false')
		);
	}
}