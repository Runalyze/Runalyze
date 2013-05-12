<?php
/**
 * This file contains class::ParserXMLsuuntoMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for XML from Suunto
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserXMLsuuntoMultiple extends ParserAbstractMultipleXML {
	/**
	 * Constructor will add missing root element to xml
	 * 
	 * @param string $FileContent file content
	 */
	public function __construct($FileContent) {
		parent::__construct($this->addRootElement($FileContent));
	}

	/**
	 * Add root element to xml string
	 * @param string $XmlString
	 * @return string
	 */
	private function addRootElement(&$XmlString) {
		$XmlString = substr($XmlString, strpos($XmlString, ">")+1);

		return "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<root>".$XmlString."</root>";
	}

	/**
	 * Parse XML
	 */
	protected function parseXML() {
		$XML = simplexml_load_string_utf8( $this->FileContent );

		$Parser = new ParserXMLsuuntoSingle('', $XML);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}