<?php
/**
 * This file contains class::ImporterFiletypeCSV
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.csv
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeCSV extends ImporterFiletypeAbstract {
	/**
	 * Allowed producer of XML files
	 * @var string
	 */
	const ALLOWED_PRODUCER = 'Epson';

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		if ($this->isFromEpson($String)) {
			$this->Parser = new ParserCSVepsonSingle($String);
		} else {
			$this->throwErrorForUnknownFormat();
		}
	}

	/**
	 * Is this string from Polar?
	 * @param string $String
	 * @return bool
	 */
	protected function isFromEpson(&$String) {
		return strpos($String, '[[Training]]') !== false;
	}

	/**
	 * Throw error for unknown format
	 */
	protected function throwErrorForUnknownFormat() {
		$this->Errors[] = sprintf(
			__('This file is not supported. Supported producers of %s-files: %s.'),
			'csv', self::ALLOWED_PRODUCER
		);
	}
}

ImporterWindowTabUpload::addInfo( sprintf(__('%s-files are supported from: %s'), 'csv', ImporterFiletypeCSV::ALLOWED_PRODUCER) );