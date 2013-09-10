<?php
/**
 * This file contains class::ParserKMLtomtomMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for KML from Tom Tom
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserKMLtomtomMultiple extends ParserAbstractMultipleXML {
	/**
	 * Parse KML
	 */
	protected function parseXML() {
		if ($this->XML->xpath('//gx:Track')) {
			$Parser = new ParserKMLtomtomSingle('', $this->XML);
			$Parser->parse();

			if ($Parser->failed())
				$this->addErrors( $Parser->getErrors() );
			else
				$this->addObject( $Parser->object() );
		}
	}
}