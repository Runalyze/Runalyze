<?php
/**
 * This file contains class::ParserXMLsuuntoSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

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
	 * @var boolean
	 */
	protected $UseRR = false;

	/**
	 * @var array
	 */
	protected $RRdata = array();

	/**
	 * @var int
	 */
	protected $LastRRindex = 0;

	/**
	 * @var int
	 */
	protected $CurrentRRindex = -1;

	/**
	 * @var float [s]
	 */
	protected $RRsum = 0;

	/**
	 * @var float [s]
	 */
	protected $RRcurrentSum = 0;

	/**
	 * @var int
	 */
	protected $numRRdata = 0;

	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectXML()) {
			$this->readRRdata();
			$this->parseGeneralValues();
			$this->parseOptionalValues();
			$this->parseSamples();
			$this->finishLaps();
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
		$this->addError( __('Given XML object does not contain any results. &lt;Samples&gt;-tag or &lt;header&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->header->DateTime) );

		if (!empty($this->XML->header->Activity))
			$this->guessSportID( (string)$this->XML->header->Activity );
		else
			$this->TrainingObject->setSportid( Configuration::General()->runningSport() );
	}

	/**
	 * Parse optional values
	 */
	protected function parseOptionalValues() {
		if (!empty($this->XML->header->Duration))
			$this->TrainingObject->setTimeInSeconds((int)$this->XML->header->Duration);

		if (!empty($this->XML->header->Distance))
			$this->TrainingObject->setDistance( round((int)$this->XML->header->Distance)/1000 );

		if (!empty($this->XML->header->Energy))
			$this->TrainingObject->setCalories( round((int)$this->XML->header->Energy/4184) );

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
	 * Finish laps
	 */
	protected function finishLaps() {
		$totalTime = $this->TrainingObject->getTimeInSeconds() > 0 ? $this->TrainingObject->getTimeInSeconds() : end($this->gps['time_in_s']);

		$this->TrainingObject->Splits()->addLastSplitToComplete(end($this->gps['km']), $totalTime);
	}

	/**
	 * Read elapsed time from last sample
	 * @param SimpleXMLElement $Sample
	 */
	protected function readElapsedTimeFrom(SimpleXMLElement &$Sample) {
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
		if (!empty($Sample->Events)) {
			if (!empty($Sample->Events->Lap) && !empty($Sample->Events->Lap->Distance) && !empty($Sample->Events->Lap->Duration)) {
				$this->TrainingObject->Splits()->addSplit(
					round((int)$Sample->Events->Lap->Distance)/1000,
					(int)$Sample->Events->Lap->Duration
				);
			}
		}

		if (!empty($Sample->Latitude) && !empty($Sample->Longitude)) {
			$this->Latitude  = round((float)$Sample->Latitude * 180 / pi(), 7);
			$this->Longitude = round((float)$Sample->Longitude * 180 / pi(), 7);
		}

		if ((string)$Sample->SampleType == 'periodic') {
			if (
				(!empty($Sample->Distance) && (int)$Sample->Distance <= $this->Distance)
				|| (!empty($Sample->Time) && (int)$Sample->Time <= $this->Time)
			) {
				return;
			}

			$this->Distance = (int)$Sample->Distance;
			$this->Time     = (int)$Sample->Time;

			while ($this->RRsum < $this->Time && $this->CurrentRRindex < $this->numRRdata - 1) {
				$this->CurrentRRindex++;
				$this->RRcurrentSum += $this->RRdata[$this->CurrentRRindex]/1000;
				$this->RRsum += $this->RRdata[$this->CurrentRRindex]/1000;
			}

			$this->setGPSfromSample($Sample);
		}
	}

	/**
	 * Set gps data from sample
	 * @param SimpleXMLElement $Sample
	 */
	protected function setGPSfromSample(SimpleXMLElement &$Sample) {
		if (!empty($Sample->HR)) {
			$hr = round(60*(float)$Sample->HR);
			$this->UseRR = false;
		} elseif ($this->UseRR) {
			if ($this->CurrentRRindex >= $this->LastRRindex) {
				$hr = round(60 / $this->RRcurrentSum * ($this->CurrentRRindex - $this->LastRRindex + 1));

				$this->LastRRindex = $this->CurrentRRindex;
				$this->RRcurrentSum = $this->RRdata[$this->CurrentRRindex]/1000;
			} else {
				$hr = 0;
			}
		} else {
			$hr = count($this->gps['heartrate']) > 0 ? end($this->gps['heartrate']) : 0;
		}

		$this->gps['time_in_s'][] = $this->Time;
		$this->gps['km'][]        = round((float)$Sample->Distance/1000, ParserAbstract::DISTANCE_PRECISION);
		$this->gps['latitude'][]  = $this->Latitude;
		$this->gps['longitude'][] = $this->Longitude;
		$this->gps['altitude'][]  = !empty($Sample->Altitude)
									? (int)$Sample->Altitude
									: (count($this->gps['altitude']) > 0 ? end($this->gps['altitude']) : 0);
		$this->gps['temp'][]      = !empty($Sample->Temperature)
									? round((float)$Sample->Temperature - 273.15)
									: (count($this->gps['temp']) > 0 ? end($this->gps['temp']) : 0);
		$this->gps['heartrate'][] = $hr;
		$this->gps['rpm'][]       = !empty($Sample->Cadence)
									? (float)$Sample->Cadence * 60
									: (count($this->gps['rpm']) > 0 ? end($this->gps['rpm']) : 0);
		//$this->gps['power'][] = 0;
	}

	/**
	 * Read RR data
	 */
	protected function readRRdata() {
		$this->RRdata = $this->getRRdata();

		if (!empty($this->RRdata)) {
			$this->UseRR = true;
			$this->numRRdata = count($this->RRdata);
			$this->gps['hrv'] = $this->RRdata;
		}
	}

	/**
	 * @return array
	 */
	protected function getRRdata() {
		if (!empty($this->XML->{'R-R'}) && !empty($this->XML->{'R-R'}->Data)) {
			return explode(' ', (string)$this->XML->{'R-R'}->Data);
		}

		return array();
	}
}