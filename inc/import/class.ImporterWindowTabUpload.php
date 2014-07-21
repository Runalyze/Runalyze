<?php
/**
 * This file contains class::ImporterWindowTabUpload
 * @package Runalyze\Import
 */
/**
 * Importer tab: Upload form
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class ImporterWindowTabUpload extends ImporterWindowTab {
	/**
	 * Possible file extensions
	 * @var array
	 */
	protected $Filetypes = array();

	/**
	 * Additional information for filetypes
	 * @var array
	 */
	static private $FiletypeInfo = array();

	/**
	 * CSS id
	 * @return string
	 */
	public function cssID() {
		return 'upload';
	}

	/**
	 * Title
	 * @return string
	 */
	public function title() {
		return __('Upload');
	}

	/**
	 * Display tab content
	 */
	public function displayTab() {
		$this->readPossibleFiletypes();

		include 'tpl/tpl.Importer.upload.php';

		$this->checkPermissions();
	}

	/**
	 * Read possible filetypes
	 */
	private function readPossibleFiletypes() {
		$dir = opendir(FRONTEND_PATH.'import/filetypes/');

		while ($file = readdir($dir)) {
			if (substr($file, 0, 22) == 'class.ImporterFiletype') {
				$extension = substr($file, 22, -4);

				if ($extension != 'Abstract') {
					require_once FRONTEND_PATH.'import/filetypes/'.$file;

					$this->Filetypes[] = mb_strtolower($extension);
				}
			}
		}

		closedir($dir);
	}

	/**
	 * Get additional filetype information
	 * @return array
	 */
	protected function filetypeInfo() {
		return self::$FiletypeInfo;
	}

	/**
	 * Add additional information for uploader
	 * @param string $message message to display
	 */
	static public function addInfo($message) {
		self::$FiletypeInfo[] = $message;
	}
}