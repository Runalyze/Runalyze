<?php
/**
 * This file contains class::AccountHandler
 * @package Runalyze\System
 */

use Runalyze\Configuration;

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
	static private $SALT = 'USE_YOUR_OWN';

	/**
	 * Minimum length for passwords
	 * @var int
	 */
	static public $PASS_MIN_LENGTH = 6;

	/**
	 * Minimum length for usernames
	 * @var int
	 */
	static private $USER_MIN_LENGTH = 3;

	/**
	 * Boolean flag: registration process
	 * @var type
	 */
	static public $IS_ON_REGISTER_PROCESS = false;

	/**
	 * ID for new registered user
	 * @var int
	 */
	static public $NEW_REGISTERED_ID = -1;

	/**
	 * Salt length in bytes
	 * @var int
	 */
	static public $SALT_LENGTH = 32;

	/**
	 * Array for special key values
	 * used when initializing account
	 * @var int
	 */
	static private $SPECIAL_KEYS=array();

	/**
	 * Update account-values
	 * @param string $username
	 * @param mixed $column
	 * @param mixed $value
	 */
	static private function updateAccount($username, $column, $value) {
		DB::getInstance()->stopAddingAccountID();
		DB::getInstance()->updateWhere('account', '`username`='.DB::getInstance()->escape($username).' LIMIT 1', $column, $value);
		DB::getInstance()->startAddingAccountID();
        //FIXME        Cache::delete('account'.$id,1);
	}

        /**
         * Cache Account Data from user
         */
        static private function cacheAccountData($id) {
            //Disabled Accountcache
            /*$accountdata = Cache::get('account'.$id,1);
            if(is_null($accountdata)) {
                DB::getInstance()->stopAddingAccountID();
                $accountdata = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `id`="'.(int)$id.'" LIMIT 1')->fetch();
                DB::getInstance()->startAddingAccountID();
                Cache::set('account'.$id, $accountdata, '1800',1);
            }*/
            $accountdata = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `id`="'.(int)$id.'" LIMIT 1')->fetch();
            return $accountdata;
        }

	/**
	 * Get account-data from database
	 * @param string $username
	 * @return mixed
	 */
	static public function getDataFor($username) {
                $Data = Cache::get('account'.$username,1);
                if(is_null($Data)) {
                    DB::getInstance()->stopAddingAccountID();
                    $Data = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `username`='.DB::getInstance()->escape($username).' LIMIT 1')->fetch();
                    DB::getInstance()->startAddingAccountID();
                    Cache::set('account'.$username, $Data, '3600',1);
                }
		return $Data;
	}

	/**
	 * Get account-data from database
	 * @param int $id
	 * @return mixed
	 */
	static public function getDataForId($id) {
                $Data = self::cacheAccountData($id);
		return $Data;
	}

	/**
	 * Get mail-address for a given username
	 * @param string $username
	 * @return boolean|string
	 */
	static public function getMailFor($username) {
		DB::getInstance()->stopAddingAccountID();
		$result = DB::getInstance()->query('SELECT `mail` FROM `'.PREFIX.'account` WHERE `username`='.DB::getInstance()->escape($username).' LIMIT 1')->fetch();
		DB::getInstance()->startAddingAccountID();

		if (is_array($result) && isset($result['mail']))
			return $result['mail'];

		return false;
	}

	/**
	 * Does a user with this name exist?
	 * @param string $username
	 * @return boolean
	 */
	static public function usernameExists($username) {
		return (1 == DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'account` WHERE `username`='.DB::getInstance()->escape($username).' LIMIT 1')->fetchColumn());
	}

	/**
	 * Does a user with this mail exist?
	 * @param string $mail
	 * @return boolean
	 */
	static public function mailExists($mail) {
		return (1 == DB::getInstance()->query('SELECT 1 FROM `'.PREFIX.'account` WHERE `mail`='.DB::getInstance()->escape($mail).' LIMIT 1')->fetchColumn());
	}

	/**
	 * Compares a password (given as string) with hash from database
	 * @param string $realString
	 * @param string $hashFromDb
	 * @param string $saltFromDb
	 * @return boolean
	 */
	static public function comparePasswords($realString, $hashFromDb, $saltFromDb) {
		return (self::passwordToHash($realString, $saltFromDb) == $hashFromDb);
	}

	/**
	 * Transforms a password (given as string) to internal hash
	 * @param string $realString
	 * @return string
	 */
	static private function passwordToHash($realString, $salt) {
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
	static public function getAutologinHash() {
		return md5(trim(SessionAccountHandler::getMail()).self::getRandomHash(32));
	}

	/**
	 * Try to register new user with data from $_POST
	 * @return boolean|array true for success, array with errors otherwise
	 */
	static public function tryToRegisterNewUser() {
		$errors = array();

		if (strlen($_POST['new_username']) < self::$USER_MIN_LENGTH)
			$errors[] = array('new_username' => sprintf( __('The username has to contain at least %s signs.'), self::$USER_MIN_LENGTH));

		if (self::usernameExists($_POST['new_username']))
			$errors[] = array('new_username' => __('This username is already being used.'));

		if (self::mailExists($_POST['email']))
			$errors[] = array('email' => __('This email address is already being used.'));

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
	 * Create user for no-login installation
	 */

	public static function createNewNoLoginUser() {
		self::importEmptyValuesFor(0);
		self::setSpecialConfigValuesFor(0);
	}

	/**
	 * Create a new user from post-data
	 */
	static private function createNewUserFromPost() {
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
	static public function sendPasswordLinkTo($username) {
		$account = self::getDataFor($username);

		if ($account) {
			$pwHash = self::getChangePasswordHash();
			self::updateAccount($username, array('changepw_hash', 'changepw_timelimit'), array($pwHash, time()+DAY_IN_S));

			$subject  = 'Runalyze v'.RUNALYZE_VERSION;
			$message  = sprintf( __('Did you forget your password %s?'), $account['name'])."<br><br>\r\n\r\n";
			$message .= __('You can change your password within the next 24 hours with the following link').":<br>\r\n";
			$message .= self::getChangePasswordLink($pwHash);

			if (System::sendMail($account['mail'], $subject, $message))
				return __('The link has been sent and will be valid for 24 hours.');
			else {
				$string = __('Unable to send link. Please contact the administrator.');

				if (System::isAtLocalhost()) {
					$string .= '<br>'.__('Your local server has no smtp-server. Please contact the administrator.');
					Error::getInstance()->addDebug('Link for changing password: '.self::getChangePasswordLink($pwHash));
				}

				return $string;
			}
		}

		return __('The username is unknown.');
	}


	/**
	 *
	 *
	 */
	static private function getNewSalt() {
		return self::getRandomHash(32);
	}

	/**
	 * Get hash for changing password
	 * @return string
	 */
	static private function getChangePasswordHash() {
		return self::getRandomHash();
	}

	/**
	 * Get hash.
	 * @return string
	 */
	static private function getRandomHash($bytes = 16) {
		return bin2hex(openssl_random_pseudo_bytes($bytes));
		// return md5(substr(time()-rand(100, 100000),5,10).substr(time()-rand(100, 100000),-5));
	}

	/**
	 * Get link for changing password
	 * @param string $hash
	 * @return string
	 */
	static private function getChangePasswordLink($hash) {
		return System::getFullDomain().'login.php?chpw='.$hash;
	}

	/**
	 * Get link for activate account
	 * @param string $hash
	 * @return string
	 */
	static private function getActivationLink($hash) {
		return System::getFullDomain().'login.php?activate='.$hash;
	}

        /**
	 * Get link for activate account
	 * @param string $hash
	 * @return string
	 */
	static private function getDeletionLink($hash) {
		return System::getFullDomain().'login.php?delete='.$hash;
	}

	/**
	 * Get username requested for changing password
	 * @return boolean|string
	 */
	static public function getUsernameForChangePasswordHash() {
		DB::getInstance()->stopAddingAccountID();
		$data = DB::getInstance()->query('
			SELECT username FROM '.PREFIX.'account
			WHERE changepw_hash='.DB::getInstance()->escape($_GET['chpw']).'
				AND changepw_timelimit>'.time().'
			LIMIT 1'
		)->fetch();
		DB::getInstance()->startAddingAccountID();

		if ($data)
			return $data['username'];

		return false;
	}

	/**
	 * Try to set new password from post-values
	 * @return mixed
	 */
	static public function tryToSetNewPassword() {
		if (!isset($_POST['chpw_hash']) || !isset($_POST['new_pw']) || !isset($_POST['new_pw_again']) || !isset($_POST['chpw_username']))
			return;

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

	static public function setNewPassword($username, $password) {
		$newSalt = self::getNewSalt();
		self::updateAccount($username,
			array('password', 'salt', 'changepw_hash', 'changepw_timelimit'),
			array(self::passwordToHash($password, $newSalt), $newSalt, '', 0));
	}

	/**
	 * Try to activate the account
	 * @return boolean
	 */
	static public function tryToActivateAccount() {
		DB::getInstance()->stopAddingAccountID();
		$Account = DB::getInstance()->query('SELECT id FROM `'.PREFIX.'account` WHERE `activation_hash`='.DB::getInstance()->escape($_GET['activate']).' LIMIT 1')->fetch();
		DB::getInstance()->startAddingAccountID();

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
	static public function tryToDeleteAccount() {
		DB::getInstance()->stopAddingAccountID();
		$Account = DB::getInstance()->exec('DELETE FROM `'.PREFIX.'account` WHERE `deletion_hash`='.DB::getInstance()->escape($_GET['delete']).' LIMIT 1');
		DB::getInstance()->startAddingAccountID();

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
	static private function importEmptyValuesFor($accountID) {
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
	static private function setAndSendActivationKeyFor($accountId, &$errors) {
		$account        = DB::getInstance()->fetchByID('account', $accountId);
		$activationHash = $account['activation_hash'];
		$activationLink = self::getActivationLink($activationHash);

		$subject  = 'Runalyze v'.RUNALYZE_VERSION;
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
	static public function setAndSendDeletionKeyFor(&$errors) {
		$account      = DB::getInstance()->fetchByID('account', SessionAccountHandler::getId());
		$deletionHash = self::getRandomHash();
		$deletionLink = self::getDeletionLink($deletionHash);

		DB::getInstance()->update('account', SessionAccountHandler::getId(), 'deletion_hash', $deletionHash, false);

		$subject  = 'Runalyze v'.RUNALYZE_VERSION;
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
	static private function setSpecialConfigValuesFor($accountId) {
		if (is_null($accountId) || $accountId < 0) {
			Error::getInstance()->addError('AccountID for special config-values not set.');
			return;
		}

		$DB = DB::getInstance();

		$whereAdd = 'AND `accountid`='.(int)$accountId;
		$columns=array('category', 'key', 'value', 'accountid');

		$DB->exec('UPDATE `'.PREFIX.'type` SET `sportid`="'.self::$SPECIAL_KEYS['RUNNING_SPORT_ID'].'" WHERE `accountid`="'.$accountId.'"');

		$DB->insert('conf', $columns, array('general','MAINSPORT', self::$SPECIAL_KEYS['MAIN_SPORT_ID'], $accountId));
		$DB->insert('conf', $columns, array('general','RUNNINGSPORT', self::$SPECIAL_KEYS['RUNNING_SPORT_ID'], $accountId));
		$DB->insert('conf', $columns, array('general','TYPE_ID_RACE', self::$SPECIAL_KEYS['TYPE_ID_RACE'], $accountId));
	}
}
