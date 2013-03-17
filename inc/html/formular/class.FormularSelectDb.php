<?php
/**
 * Class for a standard select box with options from database
 */
class FormularSelectDb extends FormularSelectBox {
	/**
	 * Load options from database
	 * @param string $Table
	 * @param string $Label
	 */
	public function loadOptionsFrom($Table, $Label) {
		$Options = Mysql::getInstance()->fetchAsArray('SELECT `id`, `'.$Label.'` as `value` FROM `'.PREFIX.$Table.'` ORDER BY `id` ASC');

		foreach ($Options as $Option)
			$this->addOption($Option['id'], $Option['value']);
	}
}