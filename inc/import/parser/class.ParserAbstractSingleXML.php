<?php
/**
 * This file contains class::ParserAbstractSingleXML
 * @package Runalyze\Import\Parser
 */

use Runalyze\Import\Exception\ParserException;

/**
 * Abstract parser for single training from xml content
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
abstract class ParserAbstractSingleXML extends ParserAbstractSingle {
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

		$this->TrainingObject = new TrainingObject( DataObject::$DEFAULT_ID );
		$this->XML = $XML;
	}

	/**
	 * Parse
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
