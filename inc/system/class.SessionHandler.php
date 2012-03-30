<?php
/**
 * This file contains the class::Account for handling account-information
 */
/**
 * Class: SessionHandler
 * 
 * @author Michael Pohl <michael@michael-pohl.info>
 * @version 0.1
 * @uses class::Error
 * @uses class::Mysql
 */
class SessionHandler {
	/**
	 * Array containing userrow from database
	 * @var array
	 */
	static private $AccountData = null;
	
	function __construct() {
		if(!isset($_SESSION['username']))
			session_start();
	}
	
	function __destruct() {
		session_destroy();
	}
	
	public function checkLogin($username, $pass) {
		$_SESSION['accountid'] = '1';
		$AccountData = Mysql::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `username` = \''.$username.'\' LIMIT 1', false);
		print_r($AccountData);
		if($AccountData) {
			$updateAccount =  Mysql::getInstance()->query('UPDATE `'.PREFIX.'account` SET `session_id` = "325" WHERE `id`="'.$AccountData['id'].'" LIMIT 1', false);
			//Nur testweise!
			$_SESSION['username'] = $username;
			$_SESSION['accountid'] = '1';
			echo $_SESSION['accountid'];
			return true;
		} else {
			return false;
		}
	}
	
	public function logged_in() {
		if(isset($_SESSION['username']))
		{
			print_r($AccountData);
			return true;
		} else {
			return false;
		}
	}
	public function logout() {
		session_destroy();
	}
	

	/**
	 * Get Userinformation from account-data
	 * @return array
	 */
	static public function getAccountData() {
		/*if (is_null(self::$AccountData)) {
			self::$AccountData = Mysql::getInstance()->fetchSingle('SELECT * FROM '.PREFIX.'account' WHERE `username` = '.$username.');
			if (self::$AccountData === false) {
				self::$AccountData = self::getDefaultArray();
				//Error::getInstance()->addNotice('Useraccount doesn\' exist');
			}
		}
	
		return self::$AccountData;*/
	}
	
	static public function getId() {
		return $AccountData['id'];
	}
	
	static public function getMail() {
		return $AccountData['mail'];
	}
	
	static public function getName() {
		return $AccountData['name'];
	}
}
?>