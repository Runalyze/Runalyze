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
	 * Get account-data from database
	 * @param string $username
	 * @return mixed
	 */
	static public function getDataFor($username) {
		return Mysql::getInstance()->fetchAsCorrectType( Mysql::getInstance()->untouchedQuery('SELECT * FROM `'.PREFIX.'account` WHERE `username`="'.$username.'" LIMIT 1') );
	}

	/**
	 * Get mail-address for a given username
	 * @param string $username
	 * @return boolean|string 
	 */
	static public function getMailFor($username) {
		$result = Mysql::getInstance()->fetchAsCorrectType( Mysql::getInstance()->untouchedQuery('SELECT mail FROM `'.PREFIX.'account` WHERE `username`="'.$username.'" LIMIT 1') );

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
		return (1 == Mysql::getInstance()->num('SELECT 1 FROM '.PREFIX.'account WHERE username="'.mysql_real_escape_string($username).'" LIMIT 1'));
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

		// TODO
		if (self::usernameExists($_POST['new_username']))
			$errors[] = 'Der Benutzername ist bereits vergeben.';
		else {
			if ($_POST['password'] != $_POST['password_again'])
				$errors[] = 'Die Passw&ouml;rter waren unterschiedlich.';
			elseif (strlen($_POST['password']) < 6)
				$errors[] = 'Das Passwort muss mindestens 6 Zeichen lang sein.';
			else {
				// 3) insert to PREFIX.'account
				// 4) import sql-files: PROBLEM - accountid is missing
				$errors[] = 'Das Registrieren von neuen Benutzern ist noch nicht m&ouml;glich.';
			}
		}

		if (empty($errors)) {
			header('Location: index.php');
			return true;
		}

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
			Mysql::getInstance()->untouchedQuery('UPDATE '.PREFIX.'account SET changepw_hash="'.$pwHash.'", changepw_timelimit="'.(time()+24*DAY_IN_S).'" WHERE username="'.$username.'" LIMIT 1');

			$subject  = 'Runalyze v'.RUNALYZE_VERSION.': Zugangsdaten';
			$message  = "Passwort vergessen, ".$account['name']."?\n\n";
			$message .= "Unter folgendem Link kannst du innerhalb der n&auml;chsten 24 Stunden dein Passwort &auml;ndern:\n";
			$message .= self::getChangePasswordLink($pwHash);
			$header   = "From: Runalyze <mail@runalyze.de>\nMIME-Version: 1.0\nContent-type: text/html; charset=UTF-8\n";

			if (mail($account['mail'], $subject, $message, $header))
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
			elseif (strlen($_POST['new_pw']) < 6)
				return array('Das Passwort muss mindestens 6 Zeichen lang sein.');
			else {
				Mysql::getInstance()->untouchedQuery('UPDATE '.PREFIX.'account SET password="'.self::passwordToHash($_POST['new_pw']).'", changepw_hash="", changepw_timelimit=0 WHERE username="'.$_POST['chpw_username'].'" LIMIT 1');
				header('Location: login.php');
			}
		} else
			return array('Da ist etwas schiefgelaufen.');
	}
}