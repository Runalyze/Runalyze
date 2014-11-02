<?php
/**
 * This file contains class::ParserSMLsuuntoSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Parser for SML files from Suunto
 *
 * @author Michael Pohl & Hannes Christiansen 
 * @see http://www.mathworks.com/matlabcentral/fileexchange/37787-suunto-ambit-data-decoder/content/AmbitDecoderVersion2.m
 * @package Runalyze\Import\Parser
 */
class ParserSMLsuuntoSingle extends ParserXMLsuuntoSingle {
	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectXML() {
		return !empty($this->XML->DeviceLog->Header) && (!empty($this->XML->DeviceLog->Samples) || !empty($this->XML->DeviceLog->samples));
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoXMLError() {
		$this->addError( __('Given XML object does not contain any results. &lt;Samples&gt;-tag or &lt;Header&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->DeviceLog->Header->DateTime) );

		if (!empty($this->XML->DeviceLog->Header->Activity))
			$this->guessSportID( (string)$this->XML->DeviceLog->Header->Activity );
		else
			$this->TrainingObject->setSportid( Configuration::General()->runningSport() );
	}

	/**
	 * Parse optional values
	 */
	protected function parseOptionalValues() {
		if (!empty($this->XML->DeviceLog->Header->Duration))
			$this->TrainingObject->setTimeInSeconds((int)$this->XML->DeviceLog->Header->Duration);

		if (!empty($this->XML->DeviceLog->Header->Distance))
			$this->TrainingObject->setDistance( round((int)$this->XML->DeviceLog->Header->Distance)/1000 );

		if (!empty($this->XML->DeviceLog->Header->Energy))
			$this->TrainingObject->setCalories( round((int)$this->XML->DeviceLog->Header->Energy/4184) );

		$this->TrainingObject->setCreatorDetails( 'Suunto' );
	}

	/**
	 * Parse samples
	 */
	protected function parseSamples() {
		if (!empty($this->XML->DeviceLog->Samples)) {
			foreach ($this->XML->DeviceLog->Samples->Sample as $Sample)
				$this->parseSample($Sample);

			$this->readElapsedTimeFrom($this->XML->DeviceLog->Samples->Sample[count($this->XML->DeviceLog->Samples->Sample)-1]);
		} elseif (!empty($this->XML->DeviceLog->samples)) {
			foreach ($this->XML->DeviceLog->samples->sample as $Sample)
				$this->parseSample($Sample);

			$this->readElapsedTimeFrom($this->XML->DeviceLog->samples->sample[count($this->XML->DeviceLog->samples->sample)-1]);
		}

		if (!empty($this->gps['altitude']) && min($this->gps['altitude']) > 0) {
			$this->TrainingObject->set('elevation_corrected', 1);
		}	
	}
}