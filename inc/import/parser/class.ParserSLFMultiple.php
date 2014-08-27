<?php
/**
 * This file contains class::ParserSLFMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for SML with multiple trainings
 *
 * @author Michael Pohl
 * @package Runalyze\Import\Parser
 */
class ParserSLFMultiple extends ParserAbstractMultipleXML {
	/**
	 * Parse XML
	 */
	protected function parseXML() {
		if (!empty($this->XML->LogEntries)) {
			$Parser = new ParserSLFSingle('', $this->XML);
			$Parser->parse();

			if ($Parser->failed())
				$this->addErrors( $Parser->getErrors() );
			else
				$this->addObject( $Parser->object() );
		}
	}
}