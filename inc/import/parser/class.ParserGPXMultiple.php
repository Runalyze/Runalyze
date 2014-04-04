<?php
/**
 * This file contains class::ParserGPXMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for GPX with multiple trainings
 * 
 * @see http://www.topografix.com/GPX/1/1/gpx.xsd
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserGPXMultiple extends ParserAbstractMultipleXML {
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
		if (isset($this->XML->trk))
			foreach ($this->XML->trk as $Track)
				$this->parseSingleTrack($Track);
	}

	/**
	 * Parse single track
	 * @param SimpleXMLElement $Track
	 */
	protected function parseSingleTrack(SimpleXMLElement &$Track) {
		$Parser = new ParserGPXSingle('', $Track);

		if (isset($this->XML->extensions))
			$Parser->setExtensionXML($this->XML->extensions);

		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}