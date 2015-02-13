<?php
/**
 * This file contains class::ParserPWXSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Parser for PWX files from Peaksware/Trainingpeaks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserPWXSingle extends ParserAbstractSingleXML {
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
		if ($this->isCorrectPWX()) {
			$this->parseGeneralValues();
			$this->parseLaps();
			$this->parseLogEntries();
			$this->setGPSarrays();
		} else {
			$this->throwNoPWXError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectPWX() {
		return isset($this->XML->device);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoPWXError() {
		$this->addError( __('Given XML object is not from Peaksware/Trainingpakes. &lt;device&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->time) );
		$this->TrainingObject->setSportid( $this->findSportId() );
		$this->TrainingObject->setCreatorDetails( $this->findCreator() );

		if (!empty($this->XML->cmt))
			$this->TrainingObject->setComment( (string)$this->XML->cmt );
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!empty($this->XML->segment))
			foreach ($this->XML->segment->summarydata as $Lap)
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
			round((int)$Lap->dist)/1000,
			round((float)$Lap->duration)
		);
	}

	/**
	 * Parse all log entries
	 */
	protected function parseLogEntries() {
		if (empty($this->XML->sample)) {
			$this->addError('Die Trainingsdatei enth&auml;lt keine Daten.');
		} else {
			foreach ($this->XML->sample as $Log)
				$this->parseLogEntry($Log);
		}
	}

	/**
	 * Parse log entry
	 * @param SimpleXMLElement $Log 
	 */
	protected function parseLogEntry($Log) {
		if ((int)$Log->timeoffset == 0)
			return;

		if (empty($Log->dist) && (empty($Log->lat) || empty($Log->lon)))
			return;

		if (!empty($Log->dist))
			$dist = (float)$Log->dist/1000;
		elseif (empty($this->gps['latitude']))
			$dist = 0;
		else
			$dist = end($this->gps['km']) + GpsData::distance(end($this->gps['latitude']), end($this->gps['longitude']),
															(double)$Log->lat, (double)$Log->lon);

		$this->gps['time_in_s'][] = (int)$Log->timeoffset;
		$this->gps['latitude'][]  = (!empty($Log->lat)) ? (double)$Log->lat : 0;
		$this->gps['longitude'][] = (!empty($Log->lon)) ? (double)$Log->lon : 0;
		$this->gps['altitude'][]  = (!empty($Log->alt)) ? round((int)$Log->alt) : 0;
		$this->gps['heartrate'][] = (!empty($Log->hr)) ? round((int)$Log->hr) : 0;
		$this->gps['km'][]        = round($dist, ParserAbstract::DISTANCE_PRECISION);
		$this->gps['pace'][]      = $this->getCurrentPace();
		$this->gps['rpm'][]       = (!empty($Log->cad)) ? (int)$Log->cad : 0;
		$this->gps['temp'][]      = (!empty($Log->temp)) ? round((int)$Log->temp) : 0;
		$this->gps['power'][]     = (!empty($Log->pwr)) ? round((int)$Log->pwr) : 0;
	}

	/**
	 * Try to get current sport id
	 * @return int 
	 */
	protected function findSportId() {
		if (!empty($this->XML->sportType)) {
			$Name = (string)$this->XML->sportType;
			$Id   = SportFactory::idByName($Name);

			if ($Id > 0)
				return $Id;
			else {
				switch ($Name) {
					case 'Run':
						$Name = 'Laufen';
						break;
					case 'Bike':
					case 'Mountain Bike':
						$Name = 'Radfahren';
						break;
					case 'Swim':
						$Name = 'Schwimmen';
						break;
					default:
						$Name = 'Sonstiges';
				}

				$Id = SportFactory::idByName($Name);

				if ($Id > 0)
					return $Id;
			}
		}

		return Configuration::General()->runningSport();
	}

	/**
	 * Get name of creator
	 * @return string
	 */
	protected function findCreator() {
		if (!empty($this->XML->device)) {
			$String = (string)$this->XML->device->make.', '.((string)$this->XML->device->model);

			return $String.' ('.((string)$this->XML->device['id']).')';
		}

		return '';
	}
}