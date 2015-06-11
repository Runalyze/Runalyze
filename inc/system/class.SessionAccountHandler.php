<?php
/**
 * This file contains class::SessionAccountHandler
 * @package Runalyze\System
 */

if (!defined('USER_MUST_LOGIN'))
/**
 * Is a login needed?
 * @var string
 */
	define('USER_MUST_LOGIN', false);

/**
 * Class: SessionAccountHandler
 * 
 * @author Michael Pohl
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class SessionAccountHandler {
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
	 * Construct a new SessionAccountHandler
	 * ATTENTION:
	 * - all used methods from constructor must not use any consts (except PREFIX)
	 * - all these consts will be defined after setting Account-ID,
	 *   because some of them need database-connection
	 */
	function __construct() {
		session_start();

		if (!$this->tryToUseSession()) {
			if ($this->tryToLoginFromPost()) {
				header('Location: '.System::getFullDomain().'index.php');
				exit;
			} elseif ($this->tryToLoginFromCookie()) {
				header('Location: '.System::getFullDomain().'index.php');
				exit;
			} elseif (self::$USER_MUST_LOGIN && !$this->isOnLoginPage() && !$this->isOnAdminPage()) {
				header('Location: '.System::getFullDomain().'login.php');
				exit;
			}
		}
	}

	/**
	 * Destruct SessionAccountHandler 
	 */
	function __destruct() {}

	/**
	 * Is user on login-page?
	 * @return boolean
	 */
	private function isOnLoginPage() {
		return substr(Request::Basename(), 0, 9) == 'login.php';
	}

	/**
	 * Is user on login-page?
	 * @return boolean
	 */
	private function isOnAdminPage() {
		return substr(Request::Basename(), 0, 9) == 'admin.php';
	}

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
			DB::getInstance()->stopAddingAccountID();
			$Account = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `id`='.(int)$_SESSION['accountid'].' LIMIT 1')->fetch();
			DB::getInstance()->startAddingAccountID();

			if ($Account['session_id'] == session_id()) {
				$this->setAccount($Account);
				$this->updateLastAction();

				Language::setLanguage($Account['language'], false);

				return true;
			} else
				unset($_SESSION['accountid']);
		}

		return false;
	}

	/**
	 * Update last action to current account 
	 */
	private function updateLastAction() {
		DB::getInstance()->update('account', self::getId(), 'lastaction', time());
	}
        
        /**
         * Update language of current account
         */
	private function updateLanguage() {
		DB::getInstance()->update('account', self::getId(), 'language', Language::getCurrentLanguage());
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

		DB::getInstance()->stopAddingAccountID();
		$Account = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `username`="'.$username.'" LIMIT 1')->fetch();
		DB::getInstance()->startAddingAccountID();

		if ($Account) {
			if (strlen($Account['activation_hash']) > 0) {
				$this->throwErrorForActivationNeeded();

				return false;
			}

			if (AccountHandler::comparePasswords($password, $Account['password'], $Account['salt'])) {

				$this->setAccount($Account);
				$this->setSession();
                                
                                //Set language for user if not exists
                                if(empty($Account['language']))
                                    $this->updateLanguage();
				// replace old md5 with new sha256 hash
				if (strlen($Account['salt']) < 1) {
					AccountHandler::setNewPassword($username, $password);
				}

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
			DB::getInstance()->stopAddingAccountID();
			$Account = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `autologin_hash`='.DB::getInstance()->escape($_COOKIE['autologin']).' LIMIT 1')->fetch();
			DB::getInstance()->startAddingAccountID();

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
	 * Try to set account from request 
	 */
	static public function setAccountFromRequest() {
		if (empty(self::$Account))
			self::$Account = AccountHandler::getDataForId( SharedLinker::getUserId() );
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
		session_regenerate_id();

		$_SESSION['username']  = self::$Account['username'];
		$_SESSION['accountid'] = self::$Account['id'];
	}
        

	/**
	 * Set session to database 
	 */
	private function setSessionToDatabase() {
		$columns = array('session_id', 'lastlogin', 'autologin_hash');
		$values  = array(session_id(), time(), $this->getAutologinHash());
		DB::getInstance()->update('account', self::getId(), $columns, $values);
	}

	/**
	 * Get autologin_hash and set it as cookie
	 * @return string
	 */
	private function getAutologinHash() {
		$autologinHash = '';

		if (isset($_POST['autologin'])) {
			$autologinHash = AccountHandler::getAutologinHash();
			setcookie('autologin', $autologinHash, time()+30*86400);
		}

		return $autologinHash;
	}

	/**
	 * Get number of online users
	 * @return int
	 */
	static public function getNumberOfUserOnline() {
		DB::getInstance()->stopAddingAccountID();
		$result = DB::getInstance()->query('SELECT COUNT(*) as num FROM '.PREFIX.'account WHERE session_id!="NULL" AND lastaction>'.(time()-10*60))->fetch();
		DB::getInstance()->startAddingAccountID();

		if ($result !== false && isset($result['num']))
			return $result['num'];

		return 0;
	}

	/**
	 * Logout 
	 */
	static public function logout() {
		DB::getInstance()->update('account', self::getId(), 'session_id', null);
		DB::getInstance()->update('account', self::getId(), 'autologin_hash', '');
		session_destroy();
		unset($_SESSION);

		setcookie('autologin', false);
		setcookie('test', false);
	}

	/**
	 * Get ID of current user
	 * @return type 
	 */
	static public function getId() {
		// Dirty hack for 'global.cleanup.php'
		if (defined('GLOBAL_CLEANUP') && class_exists('GlobalCleanupAccount'))
			return GlobalCleanupAccount::$ID;

		if (SharedLinker::isOnSharedPage())
			return SharedLinker::getUserId();

		if (!isset(self::$Account['id'])) {
			if (isset($_SESSION['accountid']))
				return $_SESSION['accountid'];

			if (USER_MUST_LOGIN)
				return null;

			return 0;
		}

		return self::$Account['id'];
	}

	/**
	 * Get mail of current user
	 * @return type 
	 */
	static public function getMail() {
		if (!isset(self::$Account['mail']))
			return '';

		return self::$Account['mail'];
	}

	/**
	 * Get name of current user
	 * @return type 
	 */
	static public function getName() {
		if (!isset(self::$Account['name']))
			return '';

		return self::$Account['name'];
	}

	/**
	 * Get name of current user
	 * @return type 
	 */
	static public function getAllowMails() {
		if (!isset(self::$Account['allow_mails']))
			return '';

		return self::$Account['allow_mails'];
	}
        
        /**
	 * Get language of current user
	 * @return type 
	 */
	static public function getLanguage() {
		if (!isset(self::$Account['languag']))
			return '';

		return self::$Account['language'];
	}
	/**
	 * Get username of current user
	 * @return type 
	 */
	static public function getUsername() {
		if (!isset(self::$Account['username']))
			return '';

		return self::$Account['username'];
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