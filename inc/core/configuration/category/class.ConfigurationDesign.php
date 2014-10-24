<?php
/**
 * This file contains class::ConfigurationDesign
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Design
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationDesign extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'design';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('DESIGN_BG_FILE', new ParameterSelectFile('img/backgrounds/Default.jpg', array(
			'folder' => 'img/backgrounds/',
			'extensions' => array('jpg', 'png', 'gif', 'jpeg', 'svg', 'tiff', 'bmp')
		)));
	}

	/**
	 * Fix background
	 * @return bool
	 */
	public function backgroundImage() {
		return $this->get('DESIGN_BG_FILE');
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('DESIGN_BG_FILE')->registerOnchangeEvent('ConfigurationDesign::setBackgroundImageToBody');
	}

	/**
	 * Fieldset
	 * @return ConfigurationFieldset
	 */
	public function Fieldset() {
		$Fieldset = new ConfigurationFieldset( __('Design') );
		$Fieldset->addHandle( $this->handle('DESIGN_BG_FILE'), array(
			'label'		=> __('Background image')
		));

		return $Fieldset;
	}

	/**
	 * Set background image to body
	 */
	static public function setBackgroundImageToBody() {
		$url = Configuration::Design()->backgroundImage();

		echo Ajax::wrapJSasFunction('$("body").css("background-image","url(\''.$url.'\')");');
	}
}