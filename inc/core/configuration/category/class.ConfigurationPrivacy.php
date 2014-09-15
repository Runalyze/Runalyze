<?php
/**
 * This file contains class::ConfigurationPrivacy
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Privacy
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationPrivacy extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'privacy';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('TRAINING_MAKE_PUBLIC', new ParameterBool(false));
		$this->createHandle('TRAINING_LIST_PUBLIC', new ParameterBool(false));
		$this->createHandle('TRAINING_LIST_ALL', new ParameterBool(false));
		$this->createHandle('TRAINING_LIST_STATISTICS', new ParameterBool(false));
		$this->createHandle('TRAINING_MAP_PUBLIC_MODE', new ActivityRoutePrivacy());
	}

	/**
	 * Publish activity
	 * @return bool
	 */
	public function publishActivity() {
		return $this->get('TRAINING_MAKE_PUBLIC');
	}

	/**
	 * List is public
	 * @return bool
	 */
	public function listIsPublic() {
		return $this->get('TRAINING_LIST_PUBLIC');
	}

	/**
	 * Show private activities in list
	 * @return bool
	 */
	public function showPrivateActivitiesInList() {
		return $this->get('TRAINING_LIST_ALL');
	}

	/**
	 * Show statistics in list
	 * @return bool
	 */
	public function showStatisticsInList() {
		return $this->get('TRAINING_LIST_STATISTICS');
	}

	/**
	 * Route privacy
	 * @return ActivityRoutePrivacy
	 */
	public function RoutePrivacy() {
		return $this->object('TRAINING_MAP_PUBLIC_MODE');
	}
}