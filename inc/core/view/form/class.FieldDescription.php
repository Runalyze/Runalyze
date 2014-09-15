<?php
/**
 * This file contains class::FieldDescription
 * @package Runalyze\View\Form
 */
/**
 * Field description
 * @author Hannes Christiansen
 * @package Runalyze\View\Form
 */
class FieldDescription {
	/**
	 * Label
	 * @var string
	 */
	protected $label;

	/**
	 * Tooltip
	 * @var string
	 */
	protected $tooltip;

	/**
	 * Construct
	 * @param string $label
	 * @param string $tooltip
	 */
	public function __construct($label, $tooltip = '') {
		$this->label = $label;
		$this->tooltip = $tooltip;
	}

	/**
	 * Label
	 * @return string
	 */
	public function label() {
		return $this->label;
	}

	/**
	 * Tooltip
	 * @return string
	 */
	public function tooltip() {
		return $this->tooltip;
	}
}