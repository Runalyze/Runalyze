<?php
/**
 * This file contains class::HtmlTag
 * @package Runalyze\HTML
 */
/**
 * Class for HTML-tags, only used for setting id, classes and so on
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
abstract class HtmlTag {
	/**
	 * ID, must be unique
	 * @var string 
	 */
	protected $Id = '';

	/**
	 * Css-classes
	 * @var array 
	 */
	protected $cssClasses = array();

	/**
	 * All attributes
	 * @var array 
	 */
	protected $attributes = array();

	/**
	 * Set ID
	 * @param string $id 
	 */
	public function setId($id) {
		$this->Id = $id;
	}

	/**
	 * Add a css-class
	 * @param string $class 
	 */
	public function addCSSclass($class) {
		$this->cssClasses[] = $class;
	}

	/**
	 * Add an attribute
	 * @param string $name 
	 * @param string $value 
	 */
	public function addAttribute($name, $value) {
		$this->attributes[] = $name.'="'.htmlspecialchars($value).'"';
	}

	/**
	 * Get string for all attributes
	 * @return string 
	 */
	public function attributes() {
		if (!empty($this->Id))
			$this->addAttribute('id', $this->Id);

		if (!empty($this->cssClasses))
			$this->addAttribute('class', implode(' ', $this->cssClasses));

		return implode(' ', $this->attributes);
	}
}