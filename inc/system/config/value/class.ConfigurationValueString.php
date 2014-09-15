<?php
/**
 * This file contains class::ConfigurationValueString
 * @package Runalyze\System\Configuration\Value
 */
/**
 * ConfigurationValueString
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ConfigurationValueString extends ConfigurationValue {
	/**
	 * Construct a new config value
	 * @param string $Key
	 * @param array $Options 
	 */
	public function __construct($Key, $Options = array()) {
		$this->Options['size'] = FormularInput::$SIZE_FULL_INLINE;

		parent::__construct($Key, $Options);
	}
}