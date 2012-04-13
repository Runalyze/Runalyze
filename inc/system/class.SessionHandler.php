<?php
/**
 * Class: SessionHandler
 * 
 * @author Michael Pohl <michael@michael-pohl.info>
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class SessionHandler {
	/**
	 * Array containing userrow from database
	 * @var array
	 */
	static private $Account = array();

	/**
	 * Error type
	 * @var int
	 */
	static public $ErrorType = 0;

	/**
	 * Error: no error
	 * @var enum
	 */
	static public $ERROR_TYPE_NO = 0;

	/**
	 * Error: no/wrong username
	 * @var enum
	 */
	static public $ERROR_TYPE_WRONG_USERNAME = 1;

	/**
	 * Error: no/wrong password
	 * @var enum
	 */
	static public $ERROR_TYPE_WRONG_PASSWORD = 2;

	/**
	 * Construct a new SessionHandler 
	 */
	function __construct() {
		session_start();

		if (self::isLoggedIn()) {
			self::$Account = Mysql::getInstance()->untouchedFetch('SELECT * FROM `'.PREFIX.'account` WHERE `id`='.mysql_real_escape_string($_SESSION['accountid']).' LIMIT 1');
			Mysql::getInstance()->untouchedQuery('UPDATE `'.PREFIX.'account` SET `lastaction`="'.time().'" WHERE `id`="'.self::getId().'" LIMIT 1');
		} elseif (isset($_POST['username']) && isset($_POST['password']))
			if ($this->tryToLogin($_POST['username'], $_POST['password']))
				header('Location: index.php');
		elseif (isset($_COOKIE['autologin']) && $this->tryToLoginFromCookie())
			header('Location: index.php');
	}

	/**
	 * Destruct SessionHandler 
	 */
	function __destruct() {}

	/**
	 * Is anyone logged in?
	 * @return boolean 
	 */
	static public function isLoggedIn() {
		if (isset($_SESSION['accountid']))
			return true;

		return false;
	}

	/**
	 * Try to login
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function tryToLogin($username, $password) {
		if (isset($_POST['chpw_hash']))
			AccountHandler::tryToSetNewPassword ();

		Error::getInstance()->addDebug('User "'.$username.'" tries to login.');

		$Account = Mysql::getInstance()->fetchAsCorrectType( Mysql::getInstance()->untouchedQuery('SELECT * FROM `'.PREFIX.'account` WHERE `username`="'.$username.'" LIMIT 1') );
		if ($Account) {
			if (AccountHandler::comparePasswords($password, $Account['password'])) {
				self::$Account = $Account;
				$this->setSession();

				return true;
			}

			$this->throwErrorForWrongPassword();
		} else {
			$this->throwErrorForWrongUsername();
		}

		return false;
	}

	private function tryToLoginFromCookie() {
		$Account = Mysql::getInstance()->fetchAsCorrectType( Mysql::getInstance()->untouchedQuery('SELECT * FROM `'.PREFIX.'account` WHERE `autologin_hash`="'.  mysql_real_escape_string($_COOKIE['autologin']).'" LIMIT 1') );
		if ($Account) {
			self::$Account = $Account;
			$this->setSession();

			return true;
		}
	}

	/**
	 * Throw error: Wrong password 
	 */
	private function throwErrorForWrongPassword() {
		self::$ErrorType = self::$ERROR_TYPE_WRONG_PASSWORD;
	}

	/**
	 * Throw error: Wrong username 
	 */
	private function throwErrorForWrongUsername() {
		self::$ErrorType = self::$ERROR_TYPE_WRONG_USERNAME;
	}

	/**
	 * Set up session 
	 */
	private function setSession() {
		$_SESSION['username'] = self::$Account['username'];
		$_SESSION['accountid'] = self::$Account['id'];

		if (isset($_POST['autologin'])) {
			$autologinHash = AccountHandler::getAutologinHash();
			setcookie('autologin', $autologinHash, time()+30*DAY_IN_S);
		} else
			$autologinHash = '';
		

		Mysql::getInstance()->untouchedQuery('
			UPDATE `'.PREFIX.'account` SET
				`session_id`="'.session_id().'",
				`lastlogin`="'.time().'",
				`autologin_hash`="'.$autologinHash.'"
			WHERE `id`="'.self::getId().'" LIMIT 1');
	}

	/**
	 * Logout 
	 */
	static public function logout() {
		Mysql::getInstance()->update(PREFIX.'account', self::getId(), 'session_id', 0);
		session_destroy();
		unset($_SESSION);
	}

	/**
	 * Get ID of current user
	 * @return type 
	 */
	static public function getId() {
		return self::$Account['id'];
	}

	/**
	 * Get Mail of current user
	 * @return type 
	 */
	static public function getMail() {
		return self::$Account['mail'];
	}

	/**
	 * Get Name of current user
	 * @return type 
	 */
	static public function getName() {
		return self::$Account['name'];
	}
}