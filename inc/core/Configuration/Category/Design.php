<?php
/**
 * This file contains class::Design
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\SelectFile;
use Ajax;

/**
 * Configuration category: Design
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class Design extends \Runalyze\Configuration\Category {
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
		$this->createHandle('DESIGN_BG_FILE', new SelectFile('web/assets/v2.x/img/backgrounds/runalyze.jpg', array(
			'folder' => 'web/assets/v2.x/img/backgrounds/',
			'extensions' => array('jpg', 'png', 'gif', 'jpeg', 'svg', 'tiff', 'bmp')
		)));
	}

	/**
	 * Background image
	 * @return string path without leading 'web/'
	 */
	public function backgroundImage() {
		return substr($this->get('DESIGN_BG_FILE'), 4);
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('DESIGN_BG_FILE')->registerOnchangeEvent('Runalyze\\Configuration\\Category\\Design::setBackgroundImageToBody');
	}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('Design') );
		$Fieldset->addHandle( $this->handle('DESIGN_BG_FILE'), array(
			'label'		=> __('Background image')
		));

		return $Fieldset;
	}

	/**
	 * Set background image to body
	 */
	public static function setBackgroundImageToBody() {
		$url = \Runalyze\Configuration::Design()->backgroundImage();

		echo Ajax::wrapJSasFunction('$("body").css("background-image","url(\''.$url.'\')");');
	}
}