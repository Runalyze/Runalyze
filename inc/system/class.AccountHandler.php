<?php
/**
 * Class: AccountHandler
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
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
	 * Update account-values
	 * @param string $username
	 * @param mixed $column
	 * @param mixed $value 
	 */
	static private function updateAccount($username, $column, $value) {
		Mysql::getInstance()->updateWhere(PREFIX.'account', '`username`="'.$username.'" LIMIT = 1', $column, $value, false);
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
		return md5(trim(SessionHandler::getMail()).self::getChangePasswordHash());
	}

	/**
	 * Try to register new user with data from $_POST
	 * @return boolean|array true for success, array with errors otherwise 
	 */
	static public function tryToRegisterNewUser() {
		$errors = array();

		if (self::usernameExists($_POST['new_username']))
			$errors[] = 'Der Benutzername ist bereits vergeben.';
		else {
			if ($_POST['password'] != $_POST['password_again'])
				$errors[] = 'Die Passw&ouml;rter waren unterschiedlich.';
			elseif (strlen($_POST['password']) < self::$PASS_MIN_LENGTH)
				$errors[] = 'Das Passwort muss mindestens '.self::$PASS_MIN_LENGTH.' Zeichen lang sein.';
			else
				$errors = self::createNewUserFromPost();
		}

		if (empty($errors)) {
			header('Location: index.php');
			return true;
		}

		return $errors;
	}

	/**
	 * Create a new user from post-data 
	 */
	static private function createNewUserFromPost() {
		$errors = array('Das Registrieren von neuen Benutzern ist noch nicht m&ouml;glich.');
		// TODO
		// 3) insert to PREFIX.'account
		// 4) import sql-files: PROBLEM - accountid is missing
		// 5) (try to) send email with activation-key

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
			$message  = "Passwort vergessen, ".$account['name']."?\n\n";
			$message .= "Unter folgendem Link kannst du innerhalb der n&auml;chsten 24 Stunden dein Passwort &auml;ndern:\n";
			$message .= self::getChangePasswordLink($pwHash);

			if (System::sendMail($account['mail'], $subject, $message))
				return 'Der Passwort-Link wurde dir zugesandt und ist 24h g&uuml;ltig.';
			else {
				$string = 'Das Versenden der E-Mail hat nicht geklappt.';

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
}