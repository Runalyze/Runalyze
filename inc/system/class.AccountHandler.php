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

	/**
	 * Get random salt
	 */
	public static function getNewSalt() {
		return self::getRandomHash(32);
	}

	/**
	 * Get hash.
	 * @param int $bytes
	 * @return string hash of length 2*$bytes
	 */
	public static function getRandomHash($bytes = 16) {
		return bin2hex(openssl_random_pseudo_bytes($bytes));
	}

	/**
	 * Set deletion key for new user and set via email
	 * @param int $accountId
	 * @return bool
	 */
	public static function setAndSendDeletionKeyFor($accountId) {
		$account      = DB::getInstance()->fetchByID('account', $accountId);
		$deletionHash = self::getRandomHash();
		$deletionLink = self::getDeletionLink($deletionHash);

		DB::getInstance()->update('account', $accountId, 'deletion_hash', $deletionHash);

		$subject  = __('Deletion request of your RUNALYZE account');
		$message  = __('Do you really want to delete your account').' '.$account['username'].", ".$account['name']."?<br><br>\r\n\r\n";
		$message .= __('Complete the process by accessing the following link: ')."<br>\r\n";
		$message .= '<a href='.$deletionLink.'>'.$deletionLink.'</a>';

		if (!System::sendMail($account['mail'], $subject, $message)) {
			return false;
		}

		return true;
	}
}
