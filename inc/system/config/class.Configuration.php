<?php
/**
 * This file contains class::Configuration
 * @package Runalyze\System\Configuration
 */
/**
 * Configuration
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration
 */
class Configuration {
	/**
	 * Categories
	 * @var ConfigurationCategory[]
	 */
	static private $Categories = array();

	/**
	 * Load all categories
	 */
	static public function loadAll() {
		// TODO
	}

	/**
	 * User ID
	 * @return int
	 */
	static private function userID() {
		if (defined('RUNALYZE_TEST'))
			return null;

		if (AccountHandler::$IS_ON_REGISTER_PROCESS) {
			$ID = AccountHandler::$NEW_REGISTERED_ID;
		} else {
			$ID = SessionAccountHandler::getId();
		}

		if ($ID == 0 && SharedLinker::isOnSharedPage()) {
			$ID = SharedLinker::getUserId();
		}

		return (int)$ID;
	}

	/**
	 * Get category
	 * @param string $categoryName
	 * @return ConfigurationGeneral
	 */
	static private function get($categoryName) {
		if (!isset(self::$Categories[$categoryName])) {
			$Category = new $categoryName();
			$Category->setUserID(self::userID());

			self::$Categories[$categoryName] = $Category;
		}

		return self::$Categories[$categoryName];
	}

	/**
	 * General
	 * @return ConfigurationGeneral
	 */
	static public function General() {
		return self::get('ConfigurationGeneral');
	}

	/**
	 * Activity view
	 * @return ConfigurationActivityView
	 */
	static public function ActivityView() {
		return self::get('ConfigurationActivityView');
	}
}