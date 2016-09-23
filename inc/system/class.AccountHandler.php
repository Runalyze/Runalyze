<?php
/**
 * This file contains class::AccountHandler
 * @package Runalyze\System
 */

use Runalyze\Configuration;
use Runalyze\Error;
use Runalyze\Language;
use Runalyze\Parameter\Application\Timezone;

/**
 * AccountHandler
 *
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class AccountHandler {
	/**
	 * Salt for internal hash-algorithm - use your own for more security
	 * @var string
	 */
	private static $SALT = 'USE_YOUR_OWN';

	/**
	 * Minimum length for passwords
	 * @var int
	 */
	public static $PASS_MIN_LENGTH = 6;

	/**
	 * Salt length in bytes
	 * @var int
	 */
	public static $SALT_LENGTH = 32;

	/**
	 * Array for special key values
	 * used when initializing account
	 * @var int
	 */
	private static $SPECIAL_KEYS = array();

	/**
	 * Update account-values
	 * @param string $username
	 * @param mixed $column
	 * @param mixed $value
	 */
	private static function updateAccount($username, $column, $value) {
		DB::getInstance()->stopAddingAccountID();
		DB::getInstance()->updateWhere('account', '`username`='.DB::getInstance()->escape($username).' LIMIT 1', $column, $value);
		DB::getInstance()->startAddingAccountID();
	}

	/**
	 * Get account-data from database
	 * @param string $username
	 * @return array
	 */
	public static function getDataFor($username) {
		return DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `username`='.DB::getInstance()->escape($username).' LIMIT 1')->fetch();
	}

	/**
	 * Get account-data from database
	 * @param int $id
	 * @return array
	 */
	public static function getDataForId($id) {
		return DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `id`="'.(int)$id.'" LIMIT 1')->fetch();
	}
}
