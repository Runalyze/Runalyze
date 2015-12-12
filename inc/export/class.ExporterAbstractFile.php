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
	public static function Type() {
		return ExporterType::File;
	}

	/**
	 * Icon class
	 * @return string
	 */
	public static function IconClass() {
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
	public static function fileNameStart() {
		return SessionAccountHandler::getId().'-Activity_';
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
		$this->getFileDownload();

		if (count($this->getAllErrors()) > 0)
			foreach ($this->getAllErrors() as $Error)
				echo HTML::error($Error);
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
	 * Download content
	 */
	final public function getFileDownload() {
	    header("Content-Type: text/plain");
	    header("Content-Disposition: attachment; filename=".$this->getFilename()."");
	    //header('Expires: 0');
	    //header('Cache-Control: must-revalidate');
	    print $this->FileContent;
	    exit;
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