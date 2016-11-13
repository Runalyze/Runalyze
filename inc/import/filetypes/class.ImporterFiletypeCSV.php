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
	const ALLOWED_PRODUCER = 'Epson, Wahoo';

	/**
	 * @param string $string
	 */
	protected function setParserFor($string) {
		if ($this->isFromEpson($string)) {
			$this->Parser = new ParserCSVepsonSingle($string);
		} elseif ($this->isFromWahoo($string)) {
			$this->Parser = new ParserCSVwahooSingle($string);
		} else {
			$this->throwErrorForUnknownFormat('csv', self::ALLOWED_PRODUCER);
		}
	}

	/**
	 * @param string $string
	 * @return bool
	 */
	protected function isFromEpson(&$string) {
		return strpos($string, '[[Training]]') !== false;
	}

	/**
	 * @param string $string
	 * @return bool
	 */
	protected function isFromWahoo(&$string) {
		return strpos($string, 'File created by Wahoo Fitness iPhone App') !== false;
	}
}

ImporterWindowTabUpload::addInfo(sprintf(__('%s-files are supported from: %s'), 'csv', ImporterFiletypeCSV::ALLOWED_PRODUCER));
