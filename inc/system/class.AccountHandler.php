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
	 * Create a new user from post-data
	 */
	public static function createNewUserFrom($newAccountId) {
		$errors = array();
        self::importEmptyValuesFor($newAccountId);
		self::setSpecialConfigValuesFor($newAccountId);

        $mailSent = true;
        if (!self::setAndSendActivationKeyFor($newAccountId)) {
		    $mailSent = false;
		}

		return $mailSent;
	}

	/**
	 * Send password to given user
	 * @param string $username
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public static function sendPasswordLinkTo($username) {
		$account = self::getDataFor($username);

		if (!$account) {
			throw new InvalidArgumentException('Unknown username');
		}

		$pwHash = self::getChangePasswordHash();
		self::updateAccount($username, array('changepw_hash', 'changepw_timelimit'), array($pwHash, time()+DAY_IN_S));

		$subject  = __('Reset your RUNALYZE password');
		$message  = sprintf( __('Did you forget your password %s?'), $account['username'])."<br><br>\r\n\r\n";
		$message .= __('You can change your password within the next 24 hours with the following link').":<br>\r\n";
		$message .= '<a href='.self::getChangePasswordLink($pwHash).'>'.self::getChangePasswordLink($pwHash).'</a>';

		if (!System::sendMail($account['mail'], $subject, $message)) {
			// TODO: provide a log entry for the admin
			return false;
		}

		return true;
	}


	/**
	 * Get random salt
	 */
	public static function getNewSalt() {
		return self::getRandomHash(32);
	}

	/**
	 * Get hash for changing password
	 * @return string
	 */
	private static function getChangePasswordHash() {
		return self::getRandomHash();
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
	 * Get link for changing password
	 * @param string $hash
	 * @return string
	 */
	private static function getChangePasswordLink($hash) {
		return System::getFullDomainWithProtocol().Language::getCurrentLanguage().'/account/recover/'.$hash;
	}

	/**
	 * Get link for activate account
	 * @param string $hash
	 * @return string
	 */
	private static function getActivationLink($hash) {
		return System::getFullDomainWithProtocol().Language::getCurrentLanguage().'/account/activate/'.$hash;
	}

	/**
	 * Get link for activate account
	 * @param string $hash
	 * @return string
	 */
	private static function getDeletionLink($hash) {
		return System::getFullDomainWithProtocol().Language::getCurrentLanguage().'/account/delete/'.$hash;
	}

	/**
	 * Import empty values for new account
	 * Attention: $accountID has to be set here - new registered users are not in session yet!
	 * @param int $accountID
	 */
	private static function importEmptyValuesFor($accountID) {
		$DB          = DB::getInstance();
		$EmptyTables = array();

		include FRONTEND_PATH . 'system/schemes/set.emptyValues.php';

		foreach ($EmptyTables as $table => $data) {
			$columns = $data['columns'];
			$columns[] = 'accountid';
			foreach ($data['values'] as $values) {
				$special_keys = array();

				for ($i = count($values); $i > count($columns)-1; $i--) {
					$special_keys[] = array_pop($values);
				}

				$values[] = $accountID;
				$dbId = $DB->insert($table, $columns, $values);

				foreach ($special_keys as $sk) {
					self::$SPECIAL_KEYS[$sk] = $dbId;
				}
			}
		}
	}

	/**
	 * Send registration/activation mail for a new user
	 * @param int $accountId
	 * @return bool
	 */
	private static function setAndSendActivationKeyFor($accountId) {
		$account        = DB::getInstance()->fetchByID('account', $accountId);

		$subject  = __('Welcome to RUNALYZE');
		$message  = __('Thanks for your registration').', '.$account['username']."!<br><br>\r\n\r\n";

		if (!USER_DISABLE_ACCOUNT_ACTIVATION) {
		    $subject  = __('Activate your RUNALYZE Account');
		    $activationHash = $account['activation_hash'];
		    $activationLink = self::getActivationLink($activationHash);

		    $message .= sprintf( __('You can activate your account (username = %s) with the following link'), $account['username']).":<br>\r\n";
		    $message .= '<a href='.$activationLink.'>'.$activationLink.'</a>';
		}

		if (!System::sendMail($account['mail'], $subject, $message) && !USER_DISABLE_ACCOUNT_ACTIVATION) {
            DB::getInstance()->update('account', $accountId, 'activation_hash', '');
			return false;
		}

		return true;
	}

	/**
	 * Set activation key for new user and set via email
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
			// TODO: provide a log entry for the admin
			return false;
		}

		return true;
	}

	/**
	 * Set some special configuration values
	 * @param int $accountId
	 */
	private static function setSpecialConfigValuesFor($accountId) {
		if (is_null($accountId) || $accountId < 0) {
			Error::getInstance()->addError('AccountID for special config-values not set.');
			return;
		}

		$DB = DB::getInstance();

		$columns = array('category', 'key', 'value', 'accountid');

		$DB->exec('UPDATE `'.PREFIX.'type` SET `sportid`="'.self::$SPECIAL_KEYS['RUNNING_SPORT_ID'].'" WHERE `accountid`="'.$accountId.'"');

		$DB->insert('conf', $columns, array('general', 'MAINSPORT', self::$SPECIAL_KEYS['MAIN_SPORT_ID'], $accountId));
		$DB->insert('conf', $columns, array('general', 'RUNNINGSPORT', self::$SPECIAL_KEYS['RUNNING_SPORT_ID'], $accountId));

		//Connect equipment type and sport
		$DB->insert('equipment_sport', array('sportid', 'equipment_typeid'), array(self::$SPECIAL_KEYS['RUNNING_SPORT_ID'], self::$SPECIAL_KEYS['EQUIPMENT_SHOES_ID']));
		$DB->insert('equipment_sport', array('sportid', 'equipment_typeid'), array(self::$SPECIAL_KEYS['RUNNING_SPORT_ID'], self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID']));

		// Use shoes as main equipment for running
		$DB->exec('UPDATE `'.PREFIX.'sport` SET `main_equipmenttypeid`="'.self::$SPECIAL_KEYS['EQUIPMENT_SHOES_ID'].'" WHERE `id`="'.self::$SPECIAL_KEYS['RUNNING_SPORT_ID'].'"');

		// Add standard clothes equipment
		$eqColumns = array('name', 'notes', 'typeid', 'accountid');
		$DB->insert('equipment', $eqColumns, array(__('long sleeve'), '', self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID'], $accountId));
		$DB->insert('equipment', $eqColumns, array(__('T-shirt'), '', self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID'], $accountId));
		$DB->insert('equipment', $eqColumns, array(__('singlet'), '', self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID'], $accountId));
		$DB->insert('equipment', $eqColumns, array(__('jacket'), '', self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID'], $accountId));
		$DB->insert('equipment', $eqColumns, array(__('long pants'), '', self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID'], $accountId));
		$DB->insert('equipment', $eqColumns, array(__('shorts'), '', self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID'], $accountId));
		$DB->insert('equipment', $eqColumns, array(__('gloves'), '', self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID'], $accountId));
		$DB->insert('equipment', $eqColumns, array(__('hat'), '', self::$SPECIAL_KEYS['EQUIPMENT_CLOTHES_ID'], $accountId));
	}
}
