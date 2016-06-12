<?php
/**
 * This file contains class::ParserAbstractMultipleXML
 * @package Runalyze\Import\Parser
 */

use Runalyze\Import\Exception\ParserException;

/**
 * Abstract parser for multiple trainings from xml content
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
abstract class ParserAbstractMultipleXML extends ParserAbstractMultiple {
	/**
	 * XML
	 * @var SimpleXMLElement
	 */
	protected $XML = null;

	/**
	 * Constructor
	 * 
	 * To construct parser directly from XML, the first parameter can be empty.
	 * 
	 * @param string $FileContent file content
	 * @param SimpleXMLElement $XML optional XML element
	 */
	public function __construct($FileContent, SimpleXMLElement $XML = null) {
		parent::__construct($FileContent);

		if (is_null($XML))
			$XML = simplexml_load_string_utf8($FileContent);

		$this->XML = $XML;
	}

	/**
	 * Parse
	 * @throws \Runalyze\Import\Exception\ParserException
	 */
	final public function parse() {
		if ($this->XML === false)
			throw new ParserException('The xml-file is not well formed and could not be parsed.');
		else
			$this->parseXML();
	}

	/**
	 * Parse XML
	 */
	abstract protected function parseXML();
}