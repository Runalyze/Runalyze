<?php
/**
 * This file contains class::ParserSLFMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for SLF with multiple trainings
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserSLF3Multiple extends ParserAbstractMultipleXML {
	/**
	 * Parse XML
	 */
	protected function parseXML() {
		if (!empty($this->XML->LogEntries)) {
			$Parser = new ParserSLF3Single('', $this->XML);
			$Parser->parse();

			if ($Parser->failed())
				$this->addErrors( $Parser->getErrors() );
			else
				$this->addObject( $Parser->object() );
		}
	}
}