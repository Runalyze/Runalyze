<?php
/**
 * This file contains class::ParserLOGBOOKSingle
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for LOGBOOK files from SportTracks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserLOGBOOKSingle extends ParserAbstractSingleXML {
	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectLOGBOOK()) {
			$this->parseValues();
		} else {
			$this->throwNoLOGBOOKError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectLOGBOOK() {
		return !empty($this->XML->attributes()->startTime);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoLOGBOOKError() {
		$this->addError('Given XML object is not from SportTracks. <Activity>-tag has no attribute \'startTime\'.');
	}

	/**
	 * Parse general values
	 */
	protected function parseValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML['startTime']) );

		if (!empty($this->XML['categoryName']))
			$this->guessSportID( (string)$this->XML['categoryName'] );
		else
			$this->TrainingObject->setSportid( CONF_MAINSPORT );

		if (!empty($this->XML['totalTime']))
			$this->TrainingObject->setTimeInSeconds( (int)$this->XML['totalTime'] );

		if (!empty($this->XML['totalDistance']))
			$this->TrainingObject->setDistance( ((int)$this->XML['totalDistance']) / 1000 );

		if (!empty($this->XML['averageHeartRate']))
			$this->TrainingObject->setPulseAvg( (int)$this->XML['averageHeartRate'] );

		if (!empty($this->XML['maximumHeartRate']))
			$this->TrainingObject->setPulseMax( (int)$this->XML['maximumHeartRate'] );

		if (!empty($this->XML['totalCalories']))
			$this->TrainingObject->setCalories( (int)$this->XML['totalCalories'] );

		if (!empty($this->XML['name']))
			$this->TrainingObject->setComment( (string)$this->XML['name'] );

		if (!empty($this->XML['notes']))
			$this->TrainingObject->setNotes( (string)$this->XML['notes'] );

		if (!empty($this->XML['location']))
			$this->TrainingObject->setRoute( (string)$this->XML['location'] );

		if (!empty($this->XML['totalAscend']))
			$this->TrainingObject->setElevation( (int)$this->XML['totalAscend'] );
	}
}