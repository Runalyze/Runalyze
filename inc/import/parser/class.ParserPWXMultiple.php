<?php
/**
 * This file contains class::ParserPWXMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for PWX with multiple trainings
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserPWXMultiple extends ParserAbstractMultipleXML {
	/**
	 * Parse XML
	 */
	protected function parseXML() {
		$this->parseWorkouts();
	}

	/**
	 * Parse standard workouts
	 */
	protected function parseWorkouts() {
		if (isset($this->XML->workout))
			foreach ($this->XML->workout as $Workout)
				$this->parseSingleWorkout($Workout);
	}

	/**
	 * Parse single workout
	 * @param SimpleXMLElement $Workout
	 */
	protected function parseSingleWorkout(SimpleXMLElement &$Workout) {
		$Parser = new ParserPWXSingle('', $Workout);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}