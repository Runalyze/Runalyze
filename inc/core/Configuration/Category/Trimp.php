<?php
/**
 * This file contains class::Trimp
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\Int;
use Ajax;

/**
 * Configuration category: Trimp
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class Trimp extends \Runalyze\Configuration\Category {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'trimp';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('ATL_DAYS', new Int(7));
		$this->createHandle('CTL_DAYS', new Int(42));
	}

	/**
	 * Days for ATL
	 * @return int
	 */
	public function daysForATL() {
		return $this->get('ATL_DAYS');
	}

	/**
	 * Days for CTL
	 * @return int
	 */
	public function daysForCTL() {
		return $this->get('CTL_DAYS');
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('ATL_DAYS')->registerOnchangeEvent('Runalyze\\Configuration\\Messages::useCleanup');
		$this->handle('ATL_DAYS')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);

		$this->handle('CTL_DAYS')->registerOnchangeEvent('Runalyze\\Configuration\\Messages::useCleanup');
		$this->handle('CTL_DAYS')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);
	}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('TRIMP') );

		$Fieldset->addHandle( $this->handle('ATL_DAYS'), array(
			'label'		=> __('Days for ATL'),
			'tooltip'	=> __('Number of days to recognize for ATL')
		));

		$Fieldset->addHandle( $this->handle('CTL_DAYS'), array(
			'label'		=> __('Days for CTL'),
			'tooltip'	=> __('Number of days to recognize for CTL')
		));

		return $Fieldset;
	}
}