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
		require_once FRONTEND_PATH.'../vendor/autoload.php';

		include FRONTEND_PATH.'system/classmap.php';

		$this->map = $CLASSMAP;

		spl_autoload_register( array($this, 'classmapLoader') );
	}

	/**
	 * Try to load class
	 * @param string $class
	 */
	protected function classmapLoader($class) {
		if (substr($class, 0, 9) == 'Runalyze\\') {
			require_once FRONTEND_PATH.'core/'.str_replace('\\', '/', substr($class, 9)).'.php';
		} elseif (substr($class, 0, 14) == 'RunalyzePlugin') {
			require_once FRONTEND_PATH.'../plugin/'.$class.'/class.'.$class.'.php';
		} elseif (isset($this->map[$class])) {
			require_once FRONTEND_PATH.$this->map[$class];
		}
	}
}
