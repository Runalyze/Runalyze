<?php
/**
 * This file contains class::ConfigurationWindow
 * @package Runalyze\System\Configuration
 */
/**
 * Configuration window
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration
 */
abstract class ConfigurationWindow {
	/**
	 * Category
	 * @var ConfigurationCategory
	 */
	protected $Category;

	/**
	 * Constructor
	 * @param ConfigurationCategory $Category
	 */
	public function __construct(ConfigurationCategory $Category) {
		$this->Category = $Category;
	}
}