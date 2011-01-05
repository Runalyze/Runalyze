<?php
/**
 * This file contains the class to handle every training.
 */
/**
 * Class: Stat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql ($mysql)
 * @uses class:Error ($error)
 * @uses $global
 *
 * Last modified 2011/01/05 15:15 by Hannes Christiansen
 */

class Stat {
	private $id,
		$data;

	function __construct($id) {
		global $error, $mysql, $global;
		if (!is_numeric($id)) {
			$error->add('ERROR', 'An object of class::Training must have an ID: <$id='.$id.'>');
			return false;
		}
		$dat = $mysql->fetch('ltb_training', $id);
		if ($dat === false) {
			$error->add('ERROR', 'This training (ID='.$id.') does not exist.');
			return false;
		}
		$this->id = $id;
		$this->data = $dat;
	}

	/**
	 * Correct the elevation data
	 */
	function elevationCorrection() {
		global $mysql, $error, $config, $global;

		// ...
	}
}
?>