<?php
/**
 * This file contains class::ConfigurationValueSelectDB
 * @package Runalyze\System\Configuration\Value
 */
/**
 * ConfigurationValueSelectDB
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ConfigurationValueSelectDB extends ConfigurationValue {
	/**
	 * All values
	 * 
	 * This array is used by RunalyzeJsonImporter to correct configuration
	 * values while importing a complete dump.
	 * 
	 * @var array
	 */
	static private $AllValues = array();

	/**
	 * All values
	 * 
	 * If you want to get all values, make sure to call
	 * <code>Configuration::loadAll()</code> first.
	 * Otherwise this method will only return all loaded values.
	 * 
	 * @return array array('key' => array('value' => ..., 'table' => ...)
	 */
	static public function allValues() {
		return self::$AllValues;
	}

	/**
	 * Reset values
	 * 
	 * This method should only be used for testing purposes.
	 */
	static public function resetValues() {
		self::$AllValues = array();
	}

	/**
	 * Set value
	 * @param mixed $value new value
	 * @throws InvalidArgumentException
	 */
	public function set($value) {
		self::$AllValues[$this->key()] = array(
			'value' => $value,
			'table' => $this->Options['table']
		);

		parent::set($value);
	}

	/**
	 * Get field
	 * @return FormularInput 
	 */
	public function getField() {
		$Field  = new FormularSelectBox($this->key(), $this->label(), $this->valueAsString());

		$Table  = $this->Options['table'];
		$Column = $this->Options['column'];
		$Values = DB::getInstance()->query('SELECT `id`,`'.DB::getInstance()->escape($Column,false).'` FROM `'.PREFIX.$Table.'` ORDER BY `'.DB::getInstance()->escape($Column,false).'` ASC')->fetchAll();

		foreach ($Values as $Value)
			$Field->addOption($Value['id'], $Value[$Column]);

		if (!empty($this->Options['layout']))
			$Field->setLayout($this->Options['layout']);

		return $Field;
	}
}