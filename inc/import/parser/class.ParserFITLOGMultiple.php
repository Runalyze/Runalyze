<?php
/**
 * This file contains class::ParserFITLOGMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for FITLOG with multiple trainings
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserFITLOGMultiple extends ParserAbstractMultipleXML {
	/**
	 * Parse XML
	 */
	protected function parseXML() {
		$this->parseActivities();
	}

	/**
	 * Parse standard activities
	 */
	protected function parseActivities() {
		if (isset($this->XML->AthleteLog->Activity))
			foreach ($this->XML->AthleteLog->Activity as $Activity)
				$this->parseSingleActivity($Activity);
	}

	/**
	 * Parse single training
	 * @param SimpleXMLElement $Activity
	 */
	protected function parseSingleActivity(SimpleXMLElement &$Activity) {
		$Parser = new ParserFITLOGSingle('', $Activity);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}