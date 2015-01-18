<?php
/**
 * This file contains class::Object
 * @package Runalyze\View
 */

namespace Runalyze\View;

/**
 * Abstract view object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View
 */
abstract class Object {
	/**
	 * Display
	 */
	public function display() {
		echo $this->code();
	}

	/**
	 * Code
	 * @return string
	 */
	abstract public function code();
}