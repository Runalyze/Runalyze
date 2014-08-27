<?php
/**
 * This file contains class::ParserSMLMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for SML with multiple trainings
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserSMLsuuntoMultiple extends ParserAbstractMultipleXML {
    
        /**
	 * Constructor will add missing root element to xml
	 * 
	 * @param string $FileContent file content
	 */
	public function __construct($FileContent) {
		parent::__construct($FileContent);
	}
        

	/**
	 * Parse XML
	 */
	protected function parseXML() {
		$XML = simplexml_load_string( $this->FileContent );

		$Parser = new ParserSMLsuuntoSingle('', $XML);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}