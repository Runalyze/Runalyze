<?php
/**
 * This file contains class::ParserXMLpolarMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for XML from Polar
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserXMLpolarMultiple extends ParserAbstractMultipleXML {
	/**
	 * Parse XML
	 */
	protected function parseXML() {
		$this->parseExercises();
	}

	/**
	 * Parse standard activities
	 */
	protected function parseExercises() {
		if (!empty($this->XML->{'calendar-items'}) && !empty($this->XML->{'calendar-items'}->exercise))
			foreach ($this->XML->{'calendar-items'}->exercise as $Exercise)
				$this->parseSingleExercise($Exercise);
	}

	/**
	 * Parse single exercise
	 * @param SimpleXMLElement $Exercise
	 */
	protected function parseSingleExercise(SimpleXMLElement &$Exercise) {
		$Parser = new ParserXMLpolarSingle('', $Exercise);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}