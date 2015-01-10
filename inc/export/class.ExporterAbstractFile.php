<?php
/**
 * This file contains class::ExporterAbstractFile
 * @package Runalyze\Export\Types
 */
/**
 * Create exporter for given type
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
abstract class ExporterAbstractFile extends ExporterAbstract {
	/**
	 * Type
	 * @return enum
	 */
	static public function Type() {
		return ExporterType::File;
	}

	/**
	 * Icon class
	 * @return string
	 */
	static public function IconClass() {
		return 'fa-file-text-o';
	}

	/**
	 * File content to write
	 * @var string
	 */
	protected $FileContent = '';

	/**
	 * Get file name start
	 * @return string
	 */
	static public function fileNameStart() {
		return SessionAccountHandler::getId().'-Training_';
	}

	/**
	 * Get extension
	 * @return string
	 */
	abstract protected function getExtension();

	/**
	 * Export
	 */
	abstract protected function setFileContent();

	/**
	 * Display
	 */
	final public function display() {
		$this->setFileContent();
		$this->writeFile();

		if (count($this->getAllErrors()) > 0)
			foreach ($this->getAllErrors() as $Error)
				echo HTML::error($Error);
		else
			echo HTML::info('
				'.__('Your activity has been exported.').'<br>
				<br>
				<a href="inc/export/files/'.$this->getFilename().'"><strong>'.__('Download').': '.$this->getFilename().'</strong></a>
			');
	}

	/**
	 * Write file 
	 */
	final protected function writeFile() {
		if (!empty($this->FileContent))
			Filesystem::writeFile('export/files/'.$this->getFilename(), $this->getFileContent());
	}

	/**
	 * Add indents to file content 
	 */
	final protected function formatFileContentAsXML() {
		$XML = new DOMDocument('1.0');
		$XML->preserveWhiteSpace = false;
		$XML->loadXML( $this->FileContent );
		$XML->formatOutput = true;

		$this->FileContent = $XML->saveXML();
	}

	/**
	 * Get file content
	 * @return string
	 */
	final public function getFileContent() {
		return $this->FileContent;
	}

	/**
	 * Get filename
	 * @return string 
	 */
	final public function getFilename() {
		if (is_null($this->Context)) {
			return 'undefined.'.$this->getExtension();
		}
	
		return self::fileNameStart().date('Y-m-d_H-i', $this->Context->activity()->timestamp()).'_'.$this->Context->activity()->id().'.'.$this->getExtension();
	}
}