<?php
/**
 * This file contains class::FormularSelectDb
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a standard select box with options from database
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularSelectDb extends FormularSelectBox {
	/**
	 * @var array
	 */
	public static $OptionsCache = array();

	/**
	 * Load options from database
	 * @param string $Table
	 * @param string $Label
	 */
	public function loadOptionsFrom($Table, $Label) {
		if (!isset(self::$OptionsCache[$Table.'.'.$Label])) {
			self::$OptionsCache[$Table.'.'.$Label] = DB::getInstance()->query(
				'SELECT `id`, `'.$Label.'` as `value` '.
				'FROM `'.PREFIX.$Table.'` '.
				'WHERE `accountid`='.\SessionAccountHandler::getId().' '.
				'ORDER BY `id` ASC'
			)->fetchAll();
		}

		foreach (self::$OptionsCache[$Table.'.'.$Label] as $Option) {
			$this->addOption($Option['id'], $Option['value']);
		}
	}
}