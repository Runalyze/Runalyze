<?php
/**
 * This file contains class::Autoloader
 * @package Runalyze\System
 */
/**
 * Autloader
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class Autoloader {
	/**
	 * Classmap
	 * @var array
	 */
	protected $map = array();

	/**
	 * Constructing a new Autloader registers all autload-functions 
	 */
	public function __construct() {
		include FRONTEND_PATH.'system/classmap.php';
		$this->map = $CLASSMAP;

		spl_autoload_register( array($this, 'classmapLoader') );
	}

	protected function classmapLoader($class) {
		if (isset($this->map[$class]))
			require_once FRONTEND_PATH.$this->map[$class];
	}
}