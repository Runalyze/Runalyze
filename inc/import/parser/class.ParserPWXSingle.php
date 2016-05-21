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
			$this->parseEvents();
			$this->applyPauses();
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
	 *
	 * "Time zones that aren't specified are considered undetermined."
	 * That means: If the time string has no appendix (no '+01:00' and no 'Z'),
	 * the offset must be treated as unknown (to prevent a timestamp change due to a coordinate-based time zone).
	 *
	 * @see http://books.xmlschemata.org/relaxng/ch19-77049.html
	 */
	protected function parseGeneralValues() {
		$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->XML->time);

		if (strlen((string)$this->XML->time) == 19) {
			$this->TrainingObject->setTimezoneOffset(null);
		}

		if (!empty($this->XML->sportType)) {
			$this->guessSportID((string)$this->XML->sportType);
		}

		$this->TrainingObject->setCreatorDetails( $this->findCreator() );

		if (!empty($this->XML->cmt))
			$this->TrainingObject->setComment( (string)$this->XML->cmt );
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!empty($this->XML->segment)) {
			foreach ($this->XML->segment as $segment) {
				foreach ($segment->summarydata as $lap) {
					$this->parseLap($lap);
				}
			}
		}
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
			$dist = end($this->gps['km']) + Runalyze\Model\Route\Entity::gpsDistance(end($this->gps['latitude']), end($this->gps['longitude']),
															(double)$Log->lat, (double)$Log->lon);

		$this->gps['time_in_s'][] = (int)$Log->timeoffset;
		$this->gps['latitude'][]  = (!empty($Log->lat)) ? (double)$Log->lat : 0;
		$this->gps['longitude'][] = (!empty($Log->lon)) ? (double)$Log->lon : 0;
		$this->gps['altitude'][]  = (!empty($Log->alt)) ? round((int)$Log->alt) : 0;
		$this->gps['heartrate'][] = (!empty($Log->hr)) ? round((int)$Log->hr) : 0;
		$this->gps['km'][]        = round($dist, ParserAbstract::DISTANCE_PRECISION);
		$this->gps['rpm'][]       = (!empty($Log->cad)) ? (int)$Log->cad : 0;
		$this->gps['temp'][]      = (!empty($Log->temp)) ? round((int)$Log->temp) : 0;
		$this->gps['power'][]     = (!empty($Log->pwr)) ? round((int)$Log->pwr) : 0;
	}

	/**
	 * Parse starting/stopping events
	 */
	protected function parseEvents() {
		$isStopped = false;
		$stopOffset = false;

		foreach ($this->XML->event as $event) {
			if ((string)$event->type == 'Starting' && $isStopped && $stopOffset !== false) {
				$this->pausesToApply[] = array(
					'time' => $stopOffset,
					'duration' => ((int)$event->timeoffset - $stopOffset)
				);

				$isStopped = false;
				$stopOffset = false;
			} elseif ((string)$event->type == 'Stopping') {
				$isStopped = true;
				$stopOffset = (int)$event->timeoffset;
			}
		}
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