<?php
/**
 * This file contains class::Configuration
 * @package Runalyze
 */

namespace Runalyze;

/**
 * Configuration
 * @author Hannes Christiansen
 * @package Runalyze
 */
class Configuration {
	/**
	 * Categories
	 * @var \Runalyze\Configuration\Category[]
	 */
	static private $Categories = array();

	/**
	 * Values from database
	 * @var array
	 */
	static private $ValuesFromDB = null;

	/**
	 * Load all categories
	 */
	static public function loadAll() {
		self::fetchAllValues();

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
	 * Fetch values
	 * @return array
	 */
	static private function fetchAllValues() {
		if (self::userID() !== null) {
			self::$ValuesFromDB = \DB::getInstance()->query('SELECT `key`,`value`,`category` FROM '.PREFIX.'conf WHERE `accountid`="'.self::userID().'"')->fetchAll();
		} else {
			self::$ValuesFromDB = array();
		}
	}

	/**
	 * User ID
	 * @return int
	 */
	static private function userID() {
		if (defined('RUNALYZE_TEST'))
			return null;

		if (\AccountHandler::$IS_ON_REGISTER_PROCESS) {
			$ID = \AccountHandler::$NEW_REGISTERED_ID;
		} else {
			$ID = \SessionAccountHandler::getId();
		}

		return $ID;
	}

	/**
	 * Get category
	 * @param string $categoryName
	 * @return \Runalyze\Configuration\Category
	 */
	static private function get($categoryName) {
		if (!isset(self::$Categories[$categoryName])) {
			$className = 'Runalyze\\Configuration\\Category\\'.$categoryName;
			$Category = new $className();
			$Category->setUserID(self::userID(), self::$ValuesFromDB);

			self::$Categories[$categoryName] = $Category;
		}

		return self::$Categories[$categoryName];
	}

	/**
	 * General
	 * @return \Runalyze\Configuration\Category\General
	 */
	static public function General() {
		return self::get('General');
	}

	/**
	 * Activity view
	 * @return \Runalyze\Configuration\Category\ActivityView
	 */
	static public function ActivityView() {
		return self::get('ActivityView');
	}

	/**
	 * Activity form
	 * @return \Runalyze\Configuration\Category\ActivityForm
	 */
	static public function ActivityForm() {
		return self::get('ActivityForm');
	}

	/**
	 * Data browser
	 * @return \Runalyze\Configuration\Category\DataBrowser
	 */
	static public function DataBrowser() {
		return self::get('DataBrowser');
	}

	/**
	 * Privacy
	 * @return \Runalyze\Configuration\Category\Privacy
	 */
	static public function Privacy() {
		return self::get('Privacy');
	}

	/**
	 * Design
	 * @return \Runalyze\Configuration\Category\Design
	 */
	static public function Design() {
		return self::get('Design');
	}

	/**
	 * Data
	 * @return \Runalyze\Configuration\Category\Data
	 */
	static public function Data() {
		return self::get('Data');
	}

	/**
	 * VDOT
	 * @return \Runalyze\Configuration\Category\Vdot
	 */
	static public function Vdot() {
		return self::get('Vdot');
	}

	/**
	 * Trimp
	 * @return \Runalyze\Configuration\Category\Trimp
	 */
	static public function Trimp() {
		return self::get('Trimp');
	}

	/**
	 * Miscellaneous
	 * @return \Runalyze\Configuration\Category\Misc
	 */
	static public function Misc() {
		return self::get('Misc');
	}
}