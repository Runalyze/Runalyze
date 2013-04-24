<?php
/**
 * This file contains class::ParserSLFSingle
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for SLF files from Sigma
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserSLFSingle extends ParserAbstractSingleXML {
	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectSLF()) {
			$this->parseGeneralValues();
			$this->parseLogEntries();
			$this->parseLaps();
			$this->setGPSarrays();
		} else {
			$this->throwNoSLFError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectSLF() {
		return !empty($this->XML->LogEntries);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoSLFError() {
		$this->addError('Given XML object is not from Sigma. <LogEntries>-tag could not be located.');
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->GeneralInformation->StartDate) );
		$this->TrainingObject->setSportid( CONF_MAINSPORT );
		$this->TrainingObject->setCreatorDetails( $this->findCreator() );
	}

	/**
	 * Parse all log entries
	 */
	protected function parseLogEntries() {
		if (empty($this->XML->LogEntries->LogEntry)) {
			$this->addError('Die Trainingsdatei enth&auml;lt keine Daten.');
		} else {
			foreach ($this->XML->LogEntries->LogEntry as $Log)
				$this->parseLogEntry($Log);
		}
	}

	/**
	 * Parse log entry
	 * @param SimpleXMLElement $Log 
	 */
	protected function parseLogEntry($Log) {
		if ((int)$Log->Time == 0 || (string)$Log->IsPause != 'false')
			return;

		$this->gps['time_in_s'][] = (int)$Log->TimeAbsolute;
		$this->gps['km'][]        = round((int)$Log->DistanceAbsolute)/1000;
		$this->gps['pace'][]      = $this->getCurrentPace();
		$this->gps['heartrate'][] = (!empty($Log->Heartrate))
									? round($Log->Heartrate)
									: 0;
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!empty($this->XML->Laps))
			foreach ($this->XML->Laps->Lap as $Lap)
				$this->parseLap($Lap);
	}

	/**
	 * Parse one single lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLap($Lap) {
		if (!empty($Lap->Calories))
			$this->TrainingObject->addCalories( (int)$Lap->Calories );

		$this->TrainingObject->Splits()->addSplit(
			round((int)$Lap->Distance)/1000,
			round((float)$Lap->Time),
			((string)$Lap->FastLap != 'false')
		);
	}

	/**
	 * Get name of creator
	 * @return string
	 */
	protected function findCreator() {
		$String = '';

		if (!empty($this->XML->GeneralInformation)) {
			foreach ($this->XML->GeneralInformation->attributes() as $key => $value)
				$String .= (string)$key.': '.((string)$value)."\n";
		}

		return $String;
	}
}