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
class ParserSLF4Multiple extends ParserAbstractMultipleXML {
	/**
	 * Parse XML
	 */
	protected function parseXML() {
		if (!empty($this->XML->Entries)) {
			$Parser = new ParserSLF4Single('', $this->XML);
			$Parser->parse();
			if ($Parser->failed())
				$this->addErrors( $Parser->getErrors() );
			else
				$this->addObject( $Parser->object() );
		}
	}
}