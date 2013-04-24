<?php
/**
 * This file contains class::ParserLOGBOOKMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for *.logbook-files from SportTracks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserLOGBOOKMultiple extends ParserAbstractMultipleXML {
	/**
	 * Parse XML
	 */
	protected function parseXML() {
		$this->parseActivities();
	}

	/**
	 * Parse all activities
	 */
	protected function parseActivities() {
		if (isset($this->XML->Activities))
			foreach ($this->XML->Activities->Activity as $Activity)
				$this->parseActivity($Activity);
	}

	/**
	 * Parse single activity
	 * @param SimpleXMLElement $Activity
	 */
	protected function parseActivity(SimpleXMLElement &$Activity) {
		$Parser = new ParserLOGBOOKSingle('', $Activity);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}