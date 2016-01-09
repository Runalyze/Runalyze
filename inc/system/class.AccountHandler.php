<?php
/**
 * This file contains class::AccountHandler
 * @package Runalyze\System
 */

use Runalyze\Configuration;
use Runalyze\Error;

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
	 * Minimum length for usernames
	 * @var int
	 */
	const USER_MIN_LENGTH = 3;

	/**
	 * @var int
	 */
	const USER_MAX_LENGTH = 32;

	/**
	 * @var string
	 */
	const USER_REGEXP = 'a-zA-Z0-9\.\_\-';

	/**
	 * Boolean flag: registration process
	 * @var bool
	 */
	public static $IS_ON_REGISTER_PROCESS = false;

	/**
	 * ID for new registered user
	 * @var int
	 */
	public static $NEW_REGISTERED_ID = -1;

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
	 * Get mail-address for a given username
	 * @param string $username
	 * @return boolean|string
	 */
	public static function getMailFor($username) {
		$result = DB::getInstance()->query('SELECT `mail` FROM `'.PREFIX.'account` WHERE `username`='.DB::getInstance()->escape($username).' LIMIT 1')->fetch();

		if (is_array($result) && isset($result['mail']))
			return $result['mail'];

		return false;
	}

	/**
	 * Does a user with this name exist?
	 * @param string $username
	 * @return boolean
	 */
	public static function usernameExists($username) {
		return (1 == DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'account` WHERE `username`='.DB::getInstance()->escape($username).' LIMIT 1')->fetchColumn());
	}

	/**
	 * Does a user with this mail exist?
	 * @param string $mail
	 * @return boolean
	 */
	public static function mailExists($mail) {
		return (1 == DB::getInstance()->query('SELECT 1 FROM `'.PREFIX.'account` WHERE `mail`='.DB::getInstance()->escape($mail).' LIMIT 1')->fetchColumn());
	}
        
        /**
         * Is the mail address valid?
         * @param string $mail
         * @return boolean 
         */
        public static function mailValid($mail) {
            $validator = new \EmailValidator\Validator();
            //isValid() could be used too, if server is connected to the internet
            return(1 == $validator->isDisposable($mail));
        }

	/**
	 * Compares a password (given as string) with hash from database
	 * @param string $realString
	 * @param string $hashFromDb
	 * @param string $saltFromDb
	 * @return boolean
	 */
	public static function comparePasswords($realString, $hashFromDb, $saltFromDb) {
		return (self::passwordToHash($realString, $saltFromDb) == $hashFromDb);
	}

	/**
	 * Transforms a password (given as string) to internal hash
	 * @param string $realString
	 * @return string
	 */
	private static function passwordToHash($realString, $salt) {
		if (is_null($salt) || strlen($salt) == 0) {
			return md5(trim($realString).self::$SALT);
		} else {
			return hash("sha256", trim($realString).$salt);
		}
	}

	/**
	 * Get hash for autologin
	 * @return string
	 */
	public static function getAutologinHash() {
		return md5(trim(SessionAccountHandler::getMail()).self::getRandomHash(32));
	}

	/**
	 * Try to register new user with data from $_POST
	 * @return boolean|array true for success, array with errors otherwise
	 */
	public static function tryToRegisterNewUser() {
		$errors = array();

		if (strlen($_POST['new_username']) < self::USER_MIN_LENGTH)
			$errors[] = array('new_username' => sprintf( __('The username has to contain at least %s signs.'), self::USER_MIN_LENGTH));

		if (strlen($_POST['new_username']) > self::USER_MAX_LENGTH)
			$errors[] = array('new_username' => sprintf( __('The username has to contain at most %s signs.'), self::USER_MAX_LENGTH));

		if (preg_replace('#[^'.self::USER_REGEXP.']#i', '', $_POST['new_username']) != $_POST['new_username'])
			$errors[] = array('new_username' => sprintf( __('The username has to contain only the following characters: %s'), stripslashes(self::USER_REGEXP)));

		if (self::usernameExists($_POST['new_username']))
			$errors[] = array('new_username' => __('This username is already being used.'));

		if (self::mailExists($_POST['email']))
			$errors[] = array('email' => __('This email address is already being used.'));
                
                if(self::mailValid($_POST['email']))
                        $errors[] = array('email' => __('This email address is not allowed'));

		if (false === filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
			$errors[] = array('email' => __('Please enter a valid email address.'));

		if ($_POST['password'] != $_POST['password_again'])
				$errors[] = array('password_again' => __('The passwords have to be the same.'));

		if (strlen($_POST['password']) < self::$PASS_MIN_LENGTH)
			$errors[] = array('password' => sprintf( __('The password has to contain at least %s characters.'), self::$PASS_MIN_LENGTH));

		if (empty($errors))
			$errors = self::createNewUserFromPost();

		if (empty($errors))
			return true;

		return $errors;
	}

	/**
	 * Create a new user from post-data
	 */
	private static function createNewUserFromPost() {
		$errors = array();

		$activationHash = (System::isAtLocalhost()) ? '' : self::getRandomHash();
		$newSalt = self::getNewSalt();
		$newAccountId   = DB::getInstance()->insert('account',
				array('username', 'name', 'mail', 'language', 'password', 'salt' , 'registerdate', 'activation_hash'),
				array($_POST['new_username'], $_POST['name'], $_POST['email'], Language::getCurrentLanguage(), self::passwordToHash($_POST['password'], $newSalt), $newSalt, time(), $activationHash));

		self::$IS_ON_REGISTER_PROCESS = true;
		self::$NEW_REGISTERED_ID = $newAccountId;

		if ($newAccountId === false)
			$errors[] = __('Something went wrong. Please contact the administrator.');
		else {
			self::importEmptyValuesFor($newAccountId);
			self::setSpecialConfigValuesFor($newAccountId);

			if ($activationHash != '')
				self::setAndSendActivationKeyFor($newAccountId, $errors);
		}

		self::$IS_ON_REGISTER_PROCESS = false;
		self::$NEW_REGISTERED_ID = -1;

		return $errors;
	}

	/**
	 * Send password to given user
	 * @param string $username
	 * @return string
	 */
	public static function sendPasswordLinkTo($username) {
		$account = self::getDataFor($username);

		if ($account) {
			$pwHash = self::getChangePasswordHash();
			self::updateAccount($username, array('changepw_hash', 'changepw_timelimit'), array($pwHash, time()+DAY_IN_S));

			$subject  = __('Reset your RUNALYZE password');
			$message  = sprintf( __('Did you forget your password %s?'), $account['name'])."<br><br>\r\n\r\n";
			$message .= __('You can change your password within the next 24 hours with the following link').":<br>\r\n";
			$message .= '<a href='.self::getChangePasswordLink($pwHash).'>'.self::getChangePasswordLink($pwHash).'</a>';

			if (System::sendMail($account['mail'], $subject, $message))
				return __('The link has been sent and will be valid for 24 hours.');
			else {
				$string = __('Unable to send link. Please contact the administrator.');

				if (System::isAtLocalhost()) {
					$string .= '<br>'.__('Your local server has no smtp-server. Please contact the administrator.');
					Error::getInstance()->addDebug('Link for changing password:'.self::getChangePasswordLink($pwHash));
				}

				return $string;
			}
		}

		return __('The username is unknown.');
	}


	/**
	 * Get random salt
	 */
	private static function getNewSalt() {
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
	 * @return string
	 */
	private static function getRandomHash($bytes = 16) {
		return bin2hex(openssl_random_pseudo_bytes($bytes));
	}

	/**
	 * Get link for changing password
	 * @param string $hash
	 * @return string
	 */
	private static function getChangePasswordLink($hash) {
		return System::getFullDomain().'login.php?chpw='.$hash;
	}

	/**
	 * Get link for activate account
	 * @param string $hash
	 * @return string
	 */
	private static function getActivationLink($hash) {
		return System::getFullDomain().'login.php?activate='.$hash;
	}

	/**
	 * Get link for activate account
	 * @param string $hash
	 * @return string
	 */
	private static function getDeletionLink($hash) {
		return System::getFullDomain().'login.php?delete='.$hash;
	}

	/**
	 * Get username requested for changing password
	 * @return boolean|string
	 */
	public static function getUsernameForChangePasswordHash() {
		$data = DB::getInstance()->query('
			SELECT username FROM '.PREFIX.'account
			WHERE changepw_hash='.DB::getInstance()->escape($_GET['chpw']).'
				AND changepw_timelimit>'.time().'
			LIMIT 1'
		)->fetch();

		if ($data)
			return $data['username'];

		return false;
	}

	/**
	 * Try to set new password from post-values
	 * @return mixed
	 */
	public static function tryToSetNewPassword() {
		if (!isset($_POST['chpw_hash']) || !isset($_POST['new_pw']) || !isset($_POST['new_pw_again']) || !isset($_POST['chpw_username']))
			return [];

		if ($_POST['chpw_username'] == self::getUsernameForChangePasswordHash()) {
			if ($_POST['new_pw'] != $_POST['new_pw_again'])
				return array( __('The passwords have to be the same.') );
			elseif (strlen($_POST['new_pw']) < self::$PASS_MIN_LENGTH)
				return array( sprintf( __('The password has to contain at least %s signs.'), self::$PASS_MIN_LENGTH) );
			else {
				self::setNewPassword($_POST['chpw_username'], $_POST['new_pw']);
				header('Location: login.php');
				exit;
			}
		} else
			return array( __('Something went wrong.') );
	}

	public static function setNewPassword($username, $password) {
		$newSalt = self::getNewSalt();
		self::updateAccount($username,
			array('password', 'salt', 'changepw_hash', 'changepw_timelimit'),
			array(self::passwordToHash($password, $newSalt), $newSalt, '', 0));
	}

	/**
	 * Try to activate the account
	 * @return boolean
	 */
	public static function tryToActivateAccount() {
		$Account = DB::getInstance()->query('SELECT id FROM `'.PREFIX.'account` WHERE `activation_hash`='.DB::getInstance()->escape($_GET['activate']).' LIMIT 1')->fetch();

		if ($Account) {
			DB::getInstance()->update('account', $Account['id'], 'activation_hash', '');

			return true;
		}

		return false;
	}

	/**
	 * Try to delete the account
	 * @return boolean
	 */
	public static function tryToDeleteAccount() {
		$Account = DB::getInstance()->exec('DELETE FROM `'.PREFIX.'account` WHERE `deletion_hash`='.DB::getInstance()->escape($_GET['delete']).' LIMIT 1');

		if ($Account) {
			return true;
		}

		return false;
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
	 * Set activation key for new user and set via email
	 * @param int $accountId
	 * @param array $errors
	 */
	private static function setAndSendActivationKeyFor($accountId, &$errors) {
		$account        = DB::getInstance()->fetchByID('account', $accountId);
		$activationHash = $account['activation_hash'];
		$activationLink = self::getActivationLink($activationHash);

		$subject  = __('Activate your RUNALYZE Account');
		$message  = __('Thanks for your registration').', '.$account['name']."!<br><br>\r\n\r\n";
		$message .= sprintf( __('You can activate your account (username = %s) with the following link'), $account['username']).":<br>\r\n";
		$message .= $activationLink;

		if (!System::sendMail($account['mail'], $subject, $message)) {
			$errors[] = __('Sending the link did not work. Please contact the administrator.');

			if (System::isAtLocalhost()) {
				if ($activationHash == '') {
					$errors[] = __('Your local server has no smtp-server. Your account has been directly activated.');
				} else {
					$errors[] = __('Your local server has no smtp-server. You have to contact the administrator.');
					Error::getInstance()->addDebug('Link for activating account: '.$activationLink);
				}
			}
		}
	}

	/**
	 * Set activation key for new user and set via email
	 * @param array $errors
	 */
	public static function setAndSendDeletionKeyFor(&$errors) {
		$account      = DB::getInstance()->fetchByID('account', SessionAccountHandler::getId());
		$deletionHash = self::getRandomHash();
		$deletionLink = self::getDeletionLink($deletionHash);

		DB::getInstance()->update('account', SessionAccountHandler::getId(), 'deletion_hash', $deletionHash);

		$subject  = __('Deletion request of your RUNALYZE account');
		$message  = __('Do you really want to delete your account').' '.$account['username'].", ".$account['name']."?<br><br>\r\n\r\n";
		$message .= __('Complete the process by accessing the following link: ')."<br>\r\n";
		$message .= $deletionLink;

		if (!System::sendMail($account['mail'], $subject, $message)) {
			$errors[] = __('Sending the link did not work. Please contact the administrator.');

			if (System::isAtLocalhost()) {
					$errors[] = __('Your local server has no smtp-server. You have to contact the administrator.');
				Error::getInstance()->addDebug('Link for deleting account: '.$deletionLink);
			}
		}
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
		$DB->insert('conf', $columns, array('general', 'TYPE_ID_RACE', self::$SPECIAL_KEYS['TYPE_ID_RACE'], $accountId));

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
