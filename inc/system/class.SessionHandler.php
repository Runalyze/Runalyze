<?php
/**
 * Class: SessionHandler
 * 
 * @author Michael Pohl <michael@michael-pohl.info>
 */
class SessionHandler {
	/**
	 * Array containing userrow from database
	 * @var array
	 */
	static private $Account = array();

	/**
	 * Construct a new SessionHandler 
	 */
	function __construct() {
		session_start();

		if (self::isLoggedIn())
			self::$Account = Mysql::getInstance()->untouchedQuery('SELECT * FROM `'.PREFIX.'account` WHERE `id`='.mysql_real_escape_string($_SESSION['accountid']).' LIMIT 1');

		// TODO: remove later on
		Error::getInstance()->addDebug(print_r($_SESSION, true));
	}

	/**
	 * Destruct SessionHandler 
	 */
	function __destruct() {
		// Destructing session does not make sense for me
		//session_destroy();
	}

	/**
	 * Try to login
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function tryToLogin($username, $password) {
		$Account = Mysql::getInstance()->untouchedQuery('SELECT * FROM `'.PREFIX.'account` WHERE `username`="'.$username.'" LIMIT 1');

		if ($Account) {
			// TODO: md5?
			if ($Account['password'] == $password) {
				self::$Account = $Account;
				$this->setSession();

				return true;
			}

			Error::getInstance()->addError('Password for user "'.$username.'" was incorrect.');

			return true;
		} else {
			Error::getInstance()->addError('Username "'.$username.'" for login is unknown.');
		}

		return false;
	}

	/**
	 * Set up session 
	 */
	private function setSession() {
		//Nur testweise!
		$_SESSION['username'] = self::$Account['username'];
		$_SESSION['accountid'] = self::$Account['id'];
		//echo $_SESSION['accountid'];

		$sessionId = 325; // TODO
		Mysql::getInstance()->update(PREFIX.'account', self::getId(), 'session_id', $sessionId);
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
	 * Logout 
	 */
	public function logout() {
		Mysql::getInstance()->update(PREFIX.'account', self::getId(), 'session_id', 0);
		session_destroy();
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