<?php
if (!defined('USER_MUST_LOGIN'))
/**
 * Is a login needed?
 * @var string
 */
	define('USER_MUST_LOGIN', false);

/**
 * Class: SessionHandler
 * 
 * @author Michael Pohl <michael@michael-pohl.info>
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class SessionHandler {
	/**
	 * Boolean flag: user must be logged in
	 * @var boolean
	 */
	static public $USER_MUST_LOGIN = USER_MUST_LOGIN;

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
	 * Error: activation needed
	 * @var enum
	 */
	static public $ERROR_TYPE_ACTIVATION_NEEDED = 3;

	/**
	 * Construct a new SessionHandler
	 * ATTENTION:
	 * - all used methods from constructor must not use any consts (except PREFIX)
	 * - all these consts will be defined after setting Account-ID,
	 *   because some of them need database-connection
	 */
	function __construct() {
		session_start();

		if (!$this->tryToUseSession()) {
			if ($this->tryToLoginFromPost())
				header('Location: index.php');
			elseif ($this->tryToLoginFromCookie())
				header('Location: index.php');
			elseif (self::$USER_MUST_LOGIN && substr(Request::Basename(), 0, 9) != 'login.php')
				header('Location: login.php');
		}
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
	 * Try to use current session
	 * @return boolean 
	 */
	private function tryToUseSession() {
		if (isset($_SESSION['accountid'])) {
			$Account = Mysql::getInstance()->untouchedFetch('SELECT * FROM `'.PREFIX.'account` WHERE `id`='.mysql_real_escape_string($_SESSION['accountid']).' LIMIT 1');

			if ($Account['session_id'] == session_id()) {
				$this->setAccount($Account);
				$this->updateLastAction();

				return true;
			}
		}

		return false;
	}

	/**
	 * Update last action to current account 
	 */
	private function updateLastAction() {
		Mysql::getInstance()->update(PREFIX.'account', self::getId(), 'lastaction', time());
	}

	/**
	 * Try to login from post data
	 * @return boolean
	 */
	private function tryToLoginFromPost() {
		if (isset($_POST['username']) && isset($_POST['password']))
			if ($this->tryToLogin($_POST['username'], $_POST['password']))
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
			AccountHandler::tryToSetNewPassword();

		$Account = Mysql::getInstance()->fetchAsCorrectType( Mysql::getInstance()->untouchedQuery('SELECT * FROM `'.PREFIX.'account` WHERE `username`="'.$username.'" LIMIT 1') );
		if ($Account) {
			if (strlen($Account['activation_hash']) > 0) {
				$this->throwErrorForActivationNeeded();

				return false;
			}

			if (AccountHandler::comparePasswords($password, $Account['password'])) {
				$this->setAccount($Account);
				$this->setSession();

				return true;
			}

			$this->throwErrorForWrongPassword();
		} else {
			$this->throwErrorForWrongUsername();
		}

		return false;
	}

	/**
	 * Try to autologin from cookie
	 * @return boolean 
	 */
	private function tryToLoginFromCookie() {
		if (isset($_COOKIE['autologin'])) {
			$Account = Mysql::getInstance()->untouchedFetch('SELECT * FROM `'.PREFIX.'account` WHERE `autologin_hash`="'.mysql_real_escape_string($_COOKIE['autologin']).'" LIMIT 1');
	
			if ($Account) {
				$this->setAccount($Account);
				$this->setSession();

				return true;
			}
		}

		return false;
	}

	/**
	 * Set internal account-data
	 * @param array $Account 
	 */
	private function setAccount($Account = array()) {
		self::$Account = $Account;
	}

	/**
	 * Set up session 
	 */
	private function setSession() {
		$this->setSessionValues();
		$this->setSessionToDatabase();
	}

	/**
	 * Set account-values to session 
	 */
	private function setSessionValues() {
		$_SESSION['username']  = self::$Account['username'];
		$_SESSION['accountid'] = self::$Account['id'];
	}

	/**
	 * Set session to database 
	 */
	private function setSessionToDatabase() {
		$columns = array('session_id', 'lastlogin', 'autologin_hash');
		$values  = array(session_id(), time(), $this->getAutologinHash());
		Mysql::getInstance()->update(PREFIX.'account', self::getId(), $columns, $values);
	}

	/**
	 * Get autologin_hash and set it as cookie
	 * @return string
	 */
	private function getAutologinHash() {
		$autologinHash = '';

		if (isset($_POST['autologin'])) {
			$autologinHash = AccountHandler::getAutologinHash();
			setcookie('autologin', $autologinHash, time()+30*DAY_IN_S);
		}

		return $autologinHash;
	}

	/**
	 * Logout 
	 */
	static public function logout() {
		Mysql::getInstance()->update(PREFIX.'account', self::getId(), 'session_id', 0);
		Mysql::getInstance()->update(PREFIX.'account', self::getId(), 'autologin_hash', '');
		session_destroy();
		unset($_SESSION);
	}

	/**
	 * Get ID of current user
	 * @return type 
	 */
	static public function getId() {
		if (!isset(self::$Account['id']))
			return 0;

		return self::$Account['id'];
	}

	/**
	 * Get Mail of current user
	 * @return type 
	 */
	static public function getMail() {
		if (!isset(self::$Account['mail']))
			return '';

		return self::$Account['mail'];
	}

	/**
	 * Get Name of current user
	 * @return type 
	 */
	static public function getName() {
		if (!isset(self::$Account['name']))
			return '';

		return self::$Account['name'];
	}

	/**
	 * Throw error: wrong password 
	 */
	private function throwErrorForWrongPassword() {
		self::$ErrorType = self::$ERROR_TYPE_WRONG_PASSWORD;
	}

	/**
	 * Throw error: wrong username 
	 */
	private function throwErrorForWrongUsername() {
		self::$ErrorType = self::$ERROR_TYPE_WRONG_USERNAME;
	}

	/**
	 * Throw error: activation needed
	 */
	private function throwErrorForActivationNeeded() {
		self::$ErrorType = self::$ERROR_TYPE_ACTIVATION_NEEDED;
	}
}