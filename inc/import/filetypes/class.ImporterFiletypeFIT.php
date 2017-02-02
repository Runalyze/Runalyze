<?php
/**
 * This file contains class::ImporterFiletypeFIT
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.fit
 *
 * Files of *.fit are from Garmin
 *
 * @author undertrained
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeFIT extends ImporterFiletypeAbstract {
	/**
	 * Parse file
	 * @param string $Filename relative path (from FRONTEND_PATH) to file
	 */
	public function parseFile($Filename) {
		$options = [
			'fix_data'		=> ['all'],
			'units'			=> 'raw',
			'garmin_timestamps'	=> false
		];
		$fit = new \adriangibbons\phpFITFileAnalysis($Filename, $options);

		$this->Parser = new ParserFITMultiple('');
		$this->Parser->setFitData($fit);
		$this->Parser->parse();
	}

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		throw new RuntimeException('ImporterFiletypeFIT does not use any parser, parseFile() has to be used instead of setParserFor().');
	}
}
