<?php
/**
 * This file contains class::ParserAbstract
 * @package Runalyze\Import\Parser
 */
/**
 * Abstract parser class
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserZIP extends ParserAbstractMultiple {
	/**
	 * Filenames
	 * @var array
	 */
	protected $Filenames = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct('');
	}

	/**
	 * Set filenames
	 * @param array $filenames
	 */
	public function setFilenames(array $filenames) {
		$this->Filenames = $filenames;
	}

	/**
	 * Parse
	 */
	public function parse() {
		if (empty($this->Filenames)) {
			return;
		}

		$Importer = new ImporterFactory($this->Filenames);
		$this->addErrors($Importer->getErrors());

		foreach ($Importer->trainingObjects() as $object) {
			$this->addObject($object);
		}
	}
}