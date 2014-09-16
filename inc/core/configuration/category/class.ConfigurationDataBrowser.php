<?php
/**
 * This file contains class::ConfigurationDataBrowser
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Data browser
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationDataBrowser extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'data-browser';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('DB_DISPLAY_MODE', new DataBrowserMode());
		$this->createHandle('DB_SHOW_DIRECT_EDIT_LINK', new ParameterBool(false));
		$this->createHandle('DB_SHOW_CREATELINK_FOR_DAYS', new ParameterBool(false));
	}

	/**
	 * Mode
	 * @return DataBrowserMode
	 */
	public function mode() {
		return $this->object('DB_DISPLAY_MODE');
	}

	/**
	 * Show edit link
	 * @return bool
	 */
	public function showEditLink() {
		return $this->get('DB_SHOW_DIRECT_EDIT_LINK');
	}

	/**
	 * Show create link
	 * @return bool
	 */
	public function showCreateLink() {
		return $this->get('DB_SHOW_CREATELINK_FOR_DAYS');
	}
}