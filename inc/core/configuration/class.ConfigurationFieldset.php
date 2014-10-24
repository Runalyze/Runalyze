<?php
/**
 * This file contains class::ConfigurationFieldset
 * @package Runalyze\Configuration
 */
/**
 * Configuration fieldset
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
 */
class ConfigurationFieldset extends FormularFieldset {
	/**
	 * Field factory
	 * @var ConfigurationFieldFactory
	 */
	protected $Factory;

	/**
	 * Construct new form
	 * @param string $title
	 */
	public function __construct($title) {
		parent::__construct($title);

		$this->Factory = new ConfigurationFieldFactory();
	}

	/**
	 * Add handle
	 * @param ConfigurationHandle $Handle
	 * @param array $options
	 */
	public function addHandle(ConfigurationHandle $Handle, array $options = array()) {
		$this->addField( $this->Factory->FieldFor($Handle, $options) );
	}
}