<?php
/**
 * This file contains class::ConfigurationTrimp
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Trimp
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationTrimp extends ConfigurationCategory {
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
		$this->createHandle('ATL_DAYS', new ParameterInt(7));
		$this->createHandle('CTL_DAYS', new ParameterInt(42));
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
}