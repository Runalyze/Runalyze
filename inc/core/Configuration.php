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
	private static $Categories = array();

	/**
	 * Values from database
	 * @var array
	 */
	private static $ValuesFromDB = null;

	/**
	 * Account ID
	 * @var int
	 */
	private static $AccountID = null;

	/**
	 * Load all categories
	 * @param mixed $accountid
	 */
	public static function loadAll($accountid = 'auto') {
		if ($accountid === 'auto') {
			self::$AccountID = self::loadAccountID();
		} else {
			self::$AccountID = $accountid;
		}

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
		self::BasicEndurance();
	}

	/**
	 * Fetch values
	 * @return array
	 */
	private static function fetchAllValues() {
		self::$Categories = array();

		if (self::$AccountID !== null) {
			self::$ValuesFromDB = \DB::getInstance()->query('SELECT `key`,`value`,`category` FROM '.PREFIX.'conf WHERE `accountid`="'.self::$AccountID.'"')->fetchAll();
		} else {
			self::$ValuesFromDB = array();
		}
	}

	/**
	 * Load account ID
	 * @return int
	 */
	private static function loadAccountID() {
		if (defined('RUNALYZE_TEST'))
			return null;

		if (\AccountHandler::$IS_ON_REGISTER_PROCESS) {
			return \AccountHandler::$NEW_REGISTERED_ID;
		}

		return \SessionAccountHandler::getId();
	}

	/**
	 * Get category
	 * @param string $categoryName
	 * @return \Runalyze\Configuration\Category
	 */
	private static function get($categoryName) {
		if (!isset(self::$Categories[$categoryName])) {
			$className = 'Runalyze\\Configuration\\Category\\'.$categoryName;
			$Category = new $className();
			$Category->setUserID(self::$AccountID, self::$ValuesFromDB);

			self::$Categories[$categoryName] = $Category;
		}

		return self::$Categories[$categoryName];
	}

	/**
	 * General
	 * @return \Runalyze\Configuration\Category\General
	 */
	public static function General() {
		return self::get('General');
	}

	/**
	 * Activity view
	 * @return \Runalyze\Configuration\Category\ActivityView
	 */
	public static function ActivityView() {
		return self::get('ActivityView');
	}

	/**
	 * Activity form
	 * @return \Runalyze\Configuration\Category\ActivityForm
	 */
	public static function ActivityForm() {
		return self::get('ActivityForm');
	}

	/**
	 * Data browser
	 * @return \Runalyze\Configuration\Category\DataBrowser
	 */
	public static function DataBrowser() {
		return self::get('DataBrowser');
	}

	/**
	 * Privacy
	 * @return \Runalyze\Configuration\Category\Privacy
	 */
	public static function Privacy() {
		return self::get('Privacy');
	}

	/**
	 * Design
	 * @return \Runalyze\Configuration\Category\Design
	 */
	public static function Design() {
		return self::get('Design');
	}

	/**
	 * Data
	 * @return \Runalyze\Configuration\Category\Data
	 */
	public static function Data() {
		return self::get('Data');
	}

	/**
	 * VDOT
	 * @return \Runalyze\Configuration\Category\Vdot
	 */
	public static function Vdot() {
		return self::get('Vdot');
	}

	/**
	 * Trimp
	 * @return \Runalyze\Configuration\Category\Trimp
	 */
	public static function Trimp() {
		return self::get('Trimp');
	}

	/**
	 * Basic endurance
	 * @return \Runalyze\Configuration\Category\BasicEndurance
	 */
	public static function BasicEndurance() {
		return self::get('BasicEndurance');
	}

	/**
	 * Miscellaneous
	 * @return \Runalyze\Configuration\Category\Misc
	 */
	public static function Misc() {
		return self::get('Misc');
	}
}