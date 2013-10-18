<?php
/**
 * This file contains class::AccountHandler
 * @package Runalyze\System
 */
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
	static private $PASS_MIN_LENGTH = 6;

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
	 * Update account-values
	 * @param string $username
	 * @param mixed $column
	 * @param mixed $value 
	 */
	static private function updateAccount($username, $column, $value) {
		Mysql::getInstance()->updateWhere(PREFIX.'account', '`username`="'.$username.'" LIMIT 1', $column, $value, false);
	}

	/**
	 * Get account-data from database
	 * @param string $username
	 * @return mixed
	 */
	static public function getDataFor($username) {
		return Mysql::getInstance()->untouchedFetch('SELECT * FROM `'.PREFIX.'account` WHERE `username`="'.$username.'" LIMIT 1');
	}

	/**
	 * Get account-data from database
	 * @param int $id
	 * @return mixed
	 */
	static public function getDataForId($id) {
		return Mysql::getInstance()->untouchedFetch('SELECT * FROM `'.PREFIX.'account` WHERE `id`="'.$id.'" LIMIT 1');
	}

	/**
	 * Get mail-address for a given username
	 * @param string $username
	 * @return boolean|string 
	 */
	static public function getMailFor($username) {
		$result = Mysql::getInstance()->untouchedFetch('SELECT `mail` FROM `'.PREFIX.'account` WHERE `username`="'.$username.'" LIMIT 1');

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
		return (1 == Mysql::getInstance()->num('SELECT 1 FROM `'.PREFIX.'account` WHERE `username`="'.mysql_real_escape_string($username).'" LIMIT 1'));
	}

	/**
	 * Does a user with this mail exist?
	 * @param string $mail
	 * @return boolean
	 */
	static public function mailExists($mail) {
		return (1 == Mysql::getInstance()->num('SELECT 1 FROM `'.PREFIX.'account` WHERE `mail`="'.mysql_real_escape_string($mail).'" LIMIT 1'));
	}

	/**
	 * Compares a password (given as string) with hash from database
	 * @param string $realString
	 * @param string $hashFromDb
	 * @return boolean 
	 */
	static public function comparePasswords($realString, $hashFromDb) {
		return (self::passwordToHash($realString) == $hashFromDb);
	}

	/**
	 * Transforms a password (given as string) to internal hash
	 * @param string $realString
	 * @return string 
	 */
	static private function passwordToHash($realString) {
		return md5(trim($realString).self::$SALT);
	}

	/**
	 * Get hash for autologin
	 * @return string
	 */
	static public function getAutologinHash() {
		return md5(trim(SessionAccountHandler::getMail()).self::$SALT.self::getChangePasswordHash());
	}

	/**
	 * Try to register new user with data from $_POST
	 * @return boolean|array true for success, array with errors otherwise
	 */
	static public function tryToRegisterNewUser() {
		$errors = array();

		if (strlen($_POST['new_username']) < self::$USER_MIN_LENGTH)
			$errors[] = array('new_username' => 'Der Benutzername muss mindestens '.self::$USER_MIN_LENGTH.' Zeichen lang sein.');
		if (self::usernameExists($_POST['new_username']))
			$errors[] = array('new_username' => 'Der Benutzername ist bereits vergeben.');
		if (self::mailExists($_POST['email']))
			$errors[] = array('email' => 'Die E-Mail-Adresse wird bereits verwendet.');
		if ($_POST['password'] != $_POST['password_again'])
				$errors[] = array('password_again' => 'Die Passw&ouml;rter waren unterschiedlich.');
		if (strlen($_POST['password']) < self::$PASS_MIN_LENGTH)
				$errors[] = array('password' => 'Das Passwort muss mindestens '.self::$PASS_MIN_LENGTH.' Zeichen lang sein.');

		if (empty($errors))
			$errors = self::createNewUserFromPost();

		if (empty($errors))
			return true;

		return $errors;
	}

	/**
	 * Create a new user from post-data 
	 */
	static private function createNewUserFromPost() {
		$errors = array();

		$activationHash = (System::isAtLocalhost()) ? '' : self::getRandomHash();
		$newAccountId   = Mysql::getInstance()->insert(PREFIX.'account',
				array('username', 'name', 'mail', 'password', 'registerdate', 'activation_hash'),
				array($_POST['new_username'], $_POST['name'], $_POST['email'], self::passwordToHash($_POST['password']), time(), $activationHash));

		self::$IS_ON_REGISTER_PROCESS = true;
		self::$NEW_REGISTERED_ID = $newAccountId;

		if ($newAccountId === false)
			$errors[] = 'Beim Registrieren ist etwas schiefgelaufen. Bitte benachrichtige den Administrator.';
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

			$subject  = 'Runalyze v'.RUNALYZE_VERSION.': Zugangsdaten';
			$message  = "Passwort vergessen, ".$account['name']."?<br /><br />\r\n\r\n";
			$message .= "Unter folgendem Link kannst du innerhalb der n&auml;chsten 24 Stunden dein Passwort &auml;ndern:<br />\r\n";
			$message .= self::getChangePasswordLink($pwHash);

			if (System::sendMail($account['mail'], $subject, $message))
				return 'Der Passwort-Link wurde dir zugesandt und ist 24h g&uuml;ltig.';
			else {
				$string = 'Das Versenden der E-Mail hat nicht geklappt. Bitte kontaktiere den Administrator.';

				if (System::isAtLocalhost()) {
					$string .= '<br />Dein lokaler Webserver hat vermutlich keinen SMTP-Server. Du musst per Hand in der Datenbank die &Auml;nderungen vornehmen oder dich an den Administrator wenden.';
					Error::getInstance()->addDebug('Link for changing password: '.self::getChangePasswordLink($pwHash));
				}
		
				return $string;
			}
		}

		return 'Der Benutzername ist uns nicht bekannt.';
	}

	/**
	 * Get hash for changing password
	 * @return string 
	 */
	static private function getChangePasswordHash() {
		return self::getRandomHash();
	}

	/**
	 * Get hash
	 * @return string 
	 */
	static private function getRandomHash() {
		return md5(substr(time()-rand(100, 100000),5,10).substr(time()-rand(100, 100000),-5));
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
		$data = Mysql::getInstance()->untouchedFetch('
			SELECT username FROM '.PREFIX.'account
			WHERE changepw_hash="'.mysql_real_escape_string($_GET['chpw']).'"
				AND changepw_timelimit>'.time().'
			LIMIT 1');

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
				return array('Die Passw&ouml;rter waren unterschiedlich.');
			elseif (strlen($_POST['new_pw']) < self::$PASS_MIN_LENGTH)
				return array('Das Passwort muss mindestens '.self::$PASS_MIN_LENGTH.' Zeichen lang sein.');
			else {
				self::updateAccount($_POST['chpw_username'],
					array('password', 'changepw_hash', 'changepw_timelimit'),
					array(self::passwordToHash($_POST['new_pw']), '', 0));
				header('Location: login.php');
			}
		} else
			return array('Da ist etwas schiefgelaufen.');
	}

	/**
	 * Try to activate the account
	 * @return boolean 
	 */
	static public function tryToActivateAccount() {
		$Account = Mysql::getInstance()->untouchedFetch('SELECT * FROM `'.PREFIX.'account` WHERE `activation_hash`="'.mysql_real_escape_string($_GET['activate']).'" LIMIT 1');
		if ($Account) {
			Mysql::getInstance()->update(PREFIX.'account', $Account['id'], 'activation_hash', '');

			return true;
		}

		return false;
	}
        /**
	 * Try to delete the account
	 * @return boolean 
	 */
	static public function tryToDeleteAccount() {
		$Account = Mysql::getInstance()->untouchedQuery('DELETE FROM `'.PREFIX.'account` WHERE `deletion_hash`="'.mysql_real_escape_string($_GET['delete']).'" LIMIT 1');
		if ($Account) {
			return true;
		}
	}

	/**
	 * Import empty values for new account
	 * Attention: $accountID has to be set here - new registered users are not in session yet!
	 * @param int $accountID 
	 */
	static private function importEmptyValuesFor($accountID) {
		$Mysql       = Mysql::getInstance();
		$EmptyTables = array();

		include FRONTEND_PATH.'system/schemes/set.emptyValues.php';

		foreach ($EmptyTables as $table => $data) {
			$columns   = $data['columns'];
			$columns[] = 'accountid';

			foreach ($data['values'] as $values) {
				$values[] = $accountID;
				$Mysql->insert(PREFIX.$table, $columns, $values);
			}
		}
	}

	/**
	 * Set activation key for new user and set via email
	 * @param int $accountId 
	 * @param array $errors
	 */
	static private function setAndSendActivationKeyFor($accountId, &$errors) {
		$account        = Mysql::getInstance()->fetch(PREFIX.'account', $accountId);
		$activationHash = $account['activation_hash'];
		$activationLink = self::getActivationLink($activationHash);

		$subject  = 'Runalyze v'.RUNALYZE_VERSION.': Registrierung';
		$message  = "Danke f&uuml;r deine Anmeldung, ".$account['name']."!<br /><br />\r\n\r\n";
		$message .= "Unter folgendem Link kannst du deinen Account (Benutzername: ".$account['username'].") best&auml;tigen:<br />\r\n";
		$message .= $activationLink;

		if (!System::sendMail($account['mail'], $subject, $message)) {
			$errors[] = 'Das Versenden der E-Mail hat nicht geklappt. Bitte kontaktiere den Administrator.';

			if (System::isAtLocalhost()) {
				if ($activationHash == '') {
					$errors[] = 'Da kein SMTP-Server vorhanden ist, wurde der Account direkt aktiviert.';
				} else {
					$errors[] = 'Dein lokaler Webserver hat vermutlich keinen SMTP-Server. Du musst per Hand in der Datenbank die &Auml;nderungen vornehmen oder dich an den Administrator wenden.';
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
		$account      = Mysql::getInstance()->fetch(PREFIX.'account', SessionAccountHandler::getId());
		$deletionHash = self::getRandomHash();
		$deletionLink = self::getDeletionLink($deletionHash);

		Mysql::getInstance()->update(PREFIX.'account', SessionAccountHandler::getId(), 'deletion_hash', $deletionHash, false);
                
		$subject  = 'Runalyze v'.RUNALYZE_VERSION.': Account löschen';
		$message  = "Schade, dass du deinen Account ".$account['username']." l&ouml;schen möchtest, ".$account['name']."!<br /><br />\r\n\r\n";
		$message .= "Unter folgendem Link kannst du deine Accountl&ouml;schung best&auml;tigen:<br />\r\n";
		$message .= $deletionLink;
		$message .= "\n Falls du dein Account nicht l&ouml;schen m&ouml;test, ignoriere diese Mail!<br />\r\n";

		if (!System::sendMail($account['mail'], $subject, $message)) {
			$errors[] = 'Das Versenden der E-Mail hat nicht geklappt. Bitte kontaktiere den Administrator.';

			if (System::isAtLocalhost()) {
				$errors[] = 'Dein lokaler Webserver hat vermutlich keinen SMTP-Server. Du musst per Hand in der Datenbank die &Auml;nderungen vornehmen oder dich an den Administrator wenden.';
				Error::getInstance()->addDebug('Link for deleting account: '.$deletionLink);
			}
		}
	}

	/**
	 * Set some special configuration values
	 * @param int $accountId 
	 */
	static private function setSpecialConfigValuesFor($accountId) {
		if ($accountId <= 0) {
			Error::getInstance()->addError('AccountID for special config-values not set.');
			return;
		}

		// Register all consts for new user, uses self::$NEW_REGISTERED_ID
		include FRONTEND_PATH.'system/register.consts.php';

		$Mysql = Mysql::getInstance();

		$data = $Mysql->fetchSingle('SELECT id FROM '.PREFIX.'sport WHERE accountid="'.$accountId.'" AND name="Laufen"');
		ConfigValue::update('MAINSPORT', $data['id'], $accountId);
		ConfigValue::update('RUNNINGSPORT', $data['id'], $accountId);
		$Mysql->query('UPDATE `'.PREFIX.'type` SET `sportid`="'.$data['id'].'" WHERE `accountid`="'.$accountId.'"', false);

		$data = $Mysql->fetchSingle('SELECT id FROM '.PREFIX.'type WHERE accountid="'.$accountId.'" AND name="Wettkampf"');
		ConfigValue::update('WK_TYPID', $data['id'], $accountId);

		$data = $Mysql->fetchSingle('SELECT id FROM '.PREFIX.'type WHERE accountid="'.$accountId.'" AND name="Langer Lauf"');
		ConfigValue::update('LL_TYPID', $data['id'], $accountId);

		$data = $Mysql->fetchSingle('SELECT value FROM '.PREFIX.'conf WHERE `key`="GARMIN_API_KEY" ORDER BY LENGTH(value) DESC');
		ConfigValue::update('GARMIN_API_KEY', $data['value'], $accountId);
	}
}