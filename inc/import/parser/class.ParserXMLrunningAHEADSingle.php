<?php
/**
 * This file contains class::ParserXMLrunningAHEADSingle
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for XML files from RunningAHEAD
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserXMLrunningAHEADSingle extends ParserAbstractSingleXML {
	/**
	 * Parse
	 */
	protected function parseXML() {
		$this->parseGeneralValues();
		$this->parseOptionalValues();
		$this->parseSplits();
		$this->parseWeather();
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( $this->timeFromString((string)$this->XML['time']) );

		if (isset($this->XML['typeName']))
			$this->guessSportID((string)$this->XML['typeName']);

		if (isset($this->XML['subtypeName']))
			$this->guessTypeID((string)$this->XML['subtypeName']);

		if (isset($this->XML->Distance))
			$this->TrainingObject->setDistance( $this->distanceFromUnit((double)$this->XML->Distance, (string)$this->XML->Distance['unit']) );

		if (isset($this->XML->Duration))
			$this->TrainingObject->setTimeInSeconds( (double)$this->XML->Duration['seconds'] );
	}

	/**
	 * Parse optional values
	 */
	protected function parseOptionalValues() {
		if (isset($this->XML->HeartRate)) {
			if (isset($this->XML->HeartRate->AvgHR))
				$this->TrainingObject->setPulseAvg( (int)$this->XML->HeartRate->AvgHR );
			if (isset($this->XML->HeartRate->MaxHR))
				$this->TrainingObject->setPulseMax( (int)$this->XML->HeartRate->MaxHR );
		}
	
		if (isset($this->XML->Equipment))
			$this->TrainingObject->setShoeid( ParserXMLrunningAHEADMultiple::newEquipmentId((string)$this->XML->Equipment['id']) );

		if (isset($this->XML->Route))
			$this->TrainingObject->setRoute( (string)$this->XML->Route );
	}

	/**
	 * Parse splits
	 */
	protected function parseSplits() {
		if (isset($this->XML->IntervalCollection)) {
			if ((string)$this->XML->IntervalCollection['name'] != 'GPS Interval')
				$this->TrainingObject->setComment( (string)$this->XML->IntervalCollection['name'] );

			foreach ($this->XML->IntervalCollection->Interval as $Interval) {
				if ((double)$Interval->Duration['seconds'] > 0)
					$this->TrainingObject->Splits()->addSplit(
						$this->distanceFromUnit((double)$Interval->Distance, (string)$Interval->Distance['unit']),
						round((double)$Interval->Duration['seconds']),
						$Interval['typeName'] == 'Interval'
					);
			}
		}
	}

	/**
	 * Parse weather
	 */
	protected function parseWeather() {
		if (isset($this->XML->EnvironmentalConditions)) {
			if (isset($this->XML->EnvironmentalConditions->Temperature)) {
				if ((string)$this->XML->EnvironmentalConditions->Temperature['unit'] == 'C')
					$this->TrainingObject->setTemperature( (int)$this->XML->EnvironmentalConditions->Temperature );
				elseif ((string)$this->XML->EnvironmentalConditions->Temperature['unit'] == 'F')
					$this->TrainingObject->setTemperature( ((int)$this->XML->EnvironmentalConditions->Temperature - 32) * 5/9 );
			}

			if (isset($this->XML->EnvironmentalConditions->Conditions)) {
				foreach ($this->XML->EnvironmentalConditions->Conditions->children() as $Condition) {
					$ID = \Runalyze\Data\Weather\Translator::IDfor($Condition->getName());
					if ($ID > 0)
						$this->TrainingObject->setWeatherid($ID);
				}
			}
		}

		if (isset($this->XML->Notes))
			$this->TrainingObject->setNotes( (string)$this->XML->Notes );
	}

	/**
	 * Calculate distance from unit
	 * @param mixed $Distance
	 * @param mixed $Unit
	 * @return double 
	 */
	private function distanceFromUnit($Distance, $Unit) {
		$Distance = (double)$Distance;
		$Unit     = (string)$Unit;

		switch ($Unit) {
			case 'mile':
				return 1.609344*$Distance;
			case 'm':
				return $Distance/1000;
			case 'km':
			default:
				return $Distance;
		}
	}

	/**
	 * Get time from string, correcting wrong UTC
	 * @param string $string
	 * @return int
	 */
	private function timeFromString($string) {
		$offset = strlen($string) > 10 ? date('Z', strtotime($string)) : 0;

		return strtotime($string) - $offset;
	}
}