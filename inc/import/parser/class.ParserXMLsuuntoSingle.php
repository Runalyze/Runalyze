<?php
/**
 * This file contains class::ParserXMLsuuntoSingle
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for XML files from Suunto
 *
 * @author Hannes Christiansen
 * @see http://www.mathworks.com/matlabcentral/fileexchange/37787-suunto-ambit-data-decoder/content/AmbitDecoderVersion2.m
 * @package Runalyze\Import\Parser
 */
class ParserXMLsuuntoSingle extends ParserAbstractSingleXML {
	/**
	 * Latitude
	 * @var float
	 */
	protected $Latitude = 0;

	/**
	 * Longitude
	 * @var float
	 */
	protected $Longitude = 0;

	/**
	 * Time
	 * @var int
	 */
	protected $Time = 0;

	/**
	 * Distance
	 * @var int
	 */
	protected $Distance = 0;

	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectXML()) {
			$this->parseGeneralValues();
			$this->parseOptionalValues();
			$this->parseSamples();
			$this->setGPSarrays();
		} else {
			$this->throwNoXMLError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectXML() {
		return !empty($this->XML->header) && (!empty($this->XML->Samples) || !empty($this->XML->samples));
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoXMLError() {
		$this->addError('Given XML object does not contain any results. &lt;Samples&gt;-tag or &lt;header&gt;-tag could not be located.');
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->header->DateTime) );

		if (!empty($this->XML->header->Activity))
			$this->guessSportID( (string)$this->XML->header->Activity );
		else
			$this->TrainingObject->setSportid( CONF_RUNNINGSPORT );
	}

	/**
	 * Parse optional values
	 */
	protected function parseOptionalValues() {
		if (!empty($this->XML->header->Duration))
			$this->TrainingObject->setTimeInSeconds((int)$this->XML->header->Duration);

		$this->TrainingObject->setCreatorDetails( 'Suunto' );
	}

	/**
	 * Parse samples
	 */
	protected function parseSamples() {
		if (!empty($this->XML->Samples)) {
			foreach ($this->XML->Samples->Sample as $Sample)
				$this->parseSample($Sample);

			$this->readElapsedTimeFrom($this->XML->Samples->Sample[count($this->XML->Samples->Sample)-1]);
		} elseif (!empty($this->XML->samples)) {
			foreach ($this->XML->samples->sample as $Sample)
				$this->parseSample($Sample);

			$this->readElapsedTimeFrom($this->XML->samples->sample[count($this->XML->samples->sample)-1]);
		}

		if (min($this->gps['altitude']) > 0)
			$this->TrainingObject->set('elevation_corrected', 1);
	}

	/**
	 * Read elapsed time from last sample
	 * @param SimpleXMLElement $Sample
	 */
	private function readElapsedTimeFrom(SimpleXMLElement &$Sample) {
		if (!empty($Sample->UTC)) {
			$FinishTimestamp = (int)strtotime((string)$Sample->UTC);

			if ($FinishTimestamp > $this->TrainingObject->getTimestamp())
				$this->TrainingObject->setElapsedTime( $FinishTimestamp - $this->TrainingObject->getTimestamp() );
		}
	}

	/**
	 * Parse sample
	 * @param SimpleXMLElement $Sample
	 */
	protected function parseSample(SimpleXMLElement &$Sample) {
		if (!empty($Sample->Events) && !empty($Sample->Events->Lap)) {
			if (!empty($Sample->Events->Lap->Distance) && !empty($Sample->Events->Lap->Duration)) {
				$this->TrainingObject->Splits()->addSplit(
					round((int)$Sample->Events->Lap->Distance)/1000,
					(int)$Sample->Events->Lap->Duration
				);
			}

			return;
		}

		if (!empty($Sample->Latitude) && !empty($Sample->Longitude)) {
			$this->Latitude  = round((float)$Sample->Latitude * 180 / pi(), 7);
			$this->Longitude = round((float)$Sample->Longitude * 180 / pi(), 7);
		}

		if ($this->Latitude != 0 && $this->Longitude != 0) {
			if (!empty($Sample->Distance) && !empty($Sample->Time)) {
				if ((int)$Sample->Time > $this->Time && (int)$Sample->Distance > $this->Distance) {
					$this->Distance = (int)$Sample->Distance;
					$this->Time     = (int)$Sample->Time;

					$this->setGPSfromSample($Sample);
				}
			}
		}
	}

	/**
	 * Set gps data from sample
	 * @param SimpleXMLElement $Sample
	 */
	protected function setGPSfromSample(SimpleXMLElement &$Sample) {
		$this->gps['time_in_s'][] = $this->Time;
		$this->gps['km'][]        = round((int)$Sample->Distance)/1000;
		$this->gps['pace'][]      = $this->getCurrentPace();
		$this->gps['latitude'][]  = $this->Latitude;
		$this->gps['longitude'][] = $this->Longitude;
		$this->gps['altitude'][]  = !empty($Sample->Altitude)
									? (int)$Sample->Altitude
									: (count($this->gps['altitude']) > 0 ? end($this->gps['altitude']) : 0);
		$this->gps['temp'][]      = !empty($Sample->Temperature)
									? round((float)$Sample->Temperature - 273.15)
									: (count($this->gps['temp']) > 0 ? end($this->gps['temp']) : 0);
		$this->gps['heartrate'][] = !empty($Sample->HR)
									? round(60*(float)$Sample->HR)
									: (count($this->gps['heartrate']) > 0 ? end($this->gps['heartrate']) : 0);
		$this->gps['rpm'][]       = !empty($Sample->Cadence)
									? (float)$Sample->Cadence * 120
									: (count($this->gps['rpm']) > 0 ? end($this->gps['rpm']) : 0);
		//$this->gps['power'][] = 0;
	}
}