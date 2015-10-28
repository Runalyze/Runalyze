<?php
/**
 * This file contains class::Fieldset
 * @package Runalyze\Configuration
 */

namespace Runalyze\Configuration;

/**
 * Configuration fieldset
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
 */
class Fieldset extends \FormularFieldset {
	/**
	 * Field factory
	 * @var \Runalyze\Configuration\FieldFactory
	 */
	protected $Factory;

	/**
	 * Construct new form
	 * @param string $title
	 */
	public function __construct($title) {
		parent::__construct($title);

		$this->Factory = new FieldFactory();
	}

	/**
	 * Add handle
	 * @param \Runalyze\Configuration\Handle $Handle
	 * @param array $options
	 */
	public function addHandle(Handle $Handle, array $options = array()) {
		$this->addField( $this->Factory->FieldFor($Handle, $options) );
	}
}