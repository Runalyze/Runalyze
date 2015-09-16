<?php
/**
 * This file contains class::ImporterFiletypeAbstractZIP
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.zip
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeZIP extends ImporterFiletypeAbstract {
	/**
	 * Parse file
	 * @param string $Filename relative path (from FRONTEND_PATH) to file
	 */
	public function parseFile($Filename) {
		$Archive = new ZipArchive();

		if ($Archive->open(FRONTEND_PATH.$Filename) === true) {
			$Files = array();
			for ($i = 0; $i < $Archive->numFiles; ++$i) {
				$file = $Archive->getNameIndex($i);
				$pathinfo = pathinfo($file);

				if (
					is_array($pathinfo) && isset($pathinfo['dirname']) && isset($pathinfo['extension']) &&
					$pathinfo['dirname'] == '.' && substr($file, 0, 1) != '.' &&
					ImporterFactory::canImportExtension($pathinfo['extension'])
				) {
					$Files[] = $file;
				}
			}

			$Archive->extractTo(FRONTEND_PATH.ImporterUpload::relativePath(''), $Files);
			$Archive->close();

			$this->readFiles($Files);
		}
	}

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		throw new RuntimeException('ImporterFiletypeZIP does not use any parser, parseFile() has to be used instead of setParserFor().');
	}

	/**
	 * Read files
	 * 
	 * @param array $filenames
	 */
	public function readFiles(array $filenames) {
		$this->Parser = new ParserZIP();
		$this->Parser->setFilenames($filenames);
		$this->Parser->parse();
	}
}