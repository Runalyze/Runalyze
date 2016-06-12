<?php
/**
 * This file contains class::ImporterFiletypeCSV
 * @package Runalyze\Import\Filetype
 */

use Runalyze\Import;

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
			$this->throwErrorForUnknownFormat('csv', self::ALLOWED_PRODUCER);
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
}

ImporterWindowTabUpload::addInfo( sprintf(__('%s-files are supported from: %s'), 'csv', ImporterFiletypeCSV::ALLOWED_PRODUCER) );