<?php
/**
 * This file contains class::Configuration
 * @package Runalyze\Configuration
 */
/**
 * Configuration
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
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
		self::ActivityForm();
		self::ActivityView();
		self::Data();
		self::DataBrowser();
		self::Design();
		self::General();
		self::Misc();
		self::Privacy();
		self::Trimp();
		self::Vdot();
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

	/**
	 * Activity form
	 * @return ConfigurationActivityForm
	 */
	static public function ActivityForm() {
		return self::get('ConfigurationActivityForm');
	}

	/**
	 * Data browser
	 * @return ConfigurationDataBrowser
	 */
	static public function DataBrowser() {
		return self::get('ConfigurationDataBrowser');
	}

	/**
	 * Privacy
	 * @return ConfigurationPrivacy
	 */
	static public function Privacy() {
		return self::get('ConfigurationPrivacy');
	}

	/**
	 * Design
	 * @return ConfigurationDesign
	 */
	static public function Design() {
		return self::get('ConfigurationDesign');
	}

	/**
	 * Data
	 * @return ConfigurationData
	 */
	static public function Data() {
		return self::get('ConfigurationData');
	}

	/**
	 * VDOT
	 * @return ConfigurationVdot
	 */
	static public function Vdot() {
		return self::get('ConfigurationVdot');
	}

	/**
	 * Trimp
	 * @return ConfigurationTrimp
	 */
	static public function Trimp() {
		return self::get('ConfigurationTrimp');
	}

	/**
	 * Miscellaneous
	 * @return ConfigurationMisc
	 */
	static public function Misc() {
		return self::get('ConfigurationMisc');
	}
}