<?php
/**
 * Autloader
 * @author Hannes Christiansen 
 */
class Autoloader {
	/**
	 * Constructing a new Autloader registers all autload-functions 
	 */
	public function __construct() {
		spl_autoload_register( array($this, 'autoload') );
		spl_autoload_register( array($this, 'autoloadCalculate') );
		spl_autoload_register( array($this, 'autoloadDraw') );
		spl_autoload_register( array($this, 'autoloadExport') );
		spl_autoload_register( array($this, 'autoloadHtml') );
		spl_autoload_register( array($this, 'autoloadHtmlFormular') );
		spl_autoload_register( array($this, 'autoloadImport') );
		spl_autoload_register( array($this, 'autoloadPlugin') );
		spl_autoload_register( array($this, 'autoloadSystem') );
		spl_autoload_register( array($this, 'autoloadSystemConfig') );
		spl_autoload_register( array($this, 'autoloadTraining') );
		spl_autoload_register( array($this, 'autoloadTrainingFormular') );
	}

	/**
	 * Try to load a given file
	 * @param string $file 
	 */
	private function tryToLoad($file) {
		if (file_exists(FRONTEND_PATH.$file))
			require_once FRONTEND_PATH.$file;
	}

	/**
	 * Standard Autloader: check in root folder
	 * @return boolean 
	 */
	private function autoload($className) {
		$this->tryToLoad('class.'.$className.'.php');
	}

	/**
	 * HTML-Autloader: check in /html/-folder
	 * @return boolean 
	 */
	private function autoloadCalculate($className) {
		$this->tryToLoad('calculate/class.'.$className.'.php');
	}

	/**
	 * HTML-Autloader: check in /draw/-folder
	 * @return boolean 
	 */
	private function autoloadDraw($className) {
		$this->tryToLoad('draw/class.'.$className.'.php');
	}

	/**
	 * HTML-Autloader: check in /export/-folder
	 * @return boolean 
	 */
	private function autoloadExport($className) {
		$this->tryToLoad('export/class.'.$className.'.php');
	}

	/**
	 * HTML-Autloader: check in /html/-folder
	 * @return boolean 
	 */
	private function autoloadHtml($className) {
		$this->tryToLoad('html/class.'.$className.'.php');
	}

	/**
	 * HTML-Autloader: check in /html/formular/-folder
	 * @return boolean 
	 */
	private function autoloadHtmlFormular($className) {
		$this->tryToLoad('html/formular/class.'.$className.'.php');
	}

	/**
	 * HTML-Autloader: check in /import/-folder
	 * @return boolean 
	 */
	private function autoloadImport($className) {
		$this->tryToLoad('import/class.'.$className.'.php');
	}

	/**
	 * HTML-Autloader: check in /plugin/-folder
	 * @return boolean 
	 */
	private function autoloadPlugin($className) {
		$this->tryToLoad('plugin/class.'.$className.'.php');
	}

	/**
	 * System-Autloader: check in /system/-folder
	 * @return boolean 
	 */
	private function autoloadSystem($className) {
		$this->tryToLoad('system/class.'.$className.'.php');
	}

	/**
	 * System-Autloader: check in /system/config/-folder
	 * @return boolean 
	 */
	private function autoloadSystemConfig($className) {
		$this->tryToLoad('system/config/class.'.$className.'.php');
	}

	/**
	 * Training-Autloader: check in /training/-folder
	 * @return boolean 
	 */
	private function autoloadTraining($className) {
		$this->tryToLoad('training/class.'.$className.'.php');
	}

	/**
	 * Training-Autloader: check in /training/formular/-folder
	 * @return boolean 
	 */
	private function autoloadTrainingFormular($className) {
		$this->tryToLoad('training//formular/class.'.$className.'.php');
	}
}