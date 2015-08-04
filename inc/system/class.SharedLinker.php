<?php
/**
 * This file contains class::SharedLinker
 * @package Runalyze\System
 */

use Runalyze\Configuration;

/**
 * Class for handling links to shared activities
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class SharedLinker {
	/**
	 * URL base for shared activities
	 * @var string
	 */
	static public $URL = 'shared/';

	/**
	 * User ID
	 * @var int
	 */
	static public $USER_ID = 0;

	/**
	 * Private constructor 
	 */
	private function __construct() {}

	/**
	 * Is the user on the shared page?
	 * @return boolean
	 */
	static public function isOnSharedPage() {
		return in_array('shared', explode('/', Request::Uri())) || self::isOnMetaCourseForFacebook();
	}

	/**
	 * @return bool
	 */
	static public function isOnMetaCourseForFacebook() {
		return (substr(Request::ScriptName(), -19) == 'call.MetaCourse.php');
	}

	/**
	 * Get link to a given training
	 * @param int $trainingID
	 * @return string
	 */
	static public function getToolbarLinkTo($trainingID) {
		return '<a href="'.self::getUrlFor($trainingID).'" target="_blank">'.Icon::$ATTACH.' '.__('Public link').'</a>';
	}

	/**
	 * Get link to a given training
	 * @param int $trainingID
	 * @param string $text [optional]
	 * @return string
	 */
	static public function getStandardLinkTo($trainingID, $text = null) {
		if (is_null($text)) {
			$text = Icon::$ATTACH;
		}

		return '<a href="'.self::getUrlFor($trainingID).'" target="_blank">'.$text.'</a>';
	}

	/**
	 * Get link to shared list for current user
	 * @param string $text [optional]
	 * @return string 
	 */
	static public function getListLinkForCurrentUser($text = null) {
		if (!Configuration::Privacy()->listIsPublic()) {
			return '';
		}

		if (is_null($text)) {
			$text = Icon::$ATTACH;
		}

		return '<a href="shared/'.SessionAccountHandler::getUsername().'/" target="_blank" '.Ajax::tooltip('', __('Public list'), false, true).'>'.$text.'</a>';
	}

	/**
	 * Get training ID from request
	 * @return int
	 */
	static public function getTrainingId() {
		return self::urlToId( Request::param('url') );
	}

	/**
	 * Get user ID
	 * @return int
	 */
	static public function getUserId() {
		if (self::$USER_ID <= 0) {
			if (strlen(Request::param('user')) > 0) {
				$Data = AccountHandler::getDataFor(Request::param('user'));
				self::$USER_ID = $Data['id'];
			} elseif (strlen(Request::param('url')) > 0) {
				DB::getInstance()->stopAddingAccountID();
				$Data = DB::getInstance()->query('SELECT `accountid` FROM `'.PREFIX.'training` WHERE id="'.self::getTrainingId().'" LIMIT 1')->fetch();
				DB::getInstance()->startAddingAccountID();

				self::$USER_ID = $Data['accountid'];
			} elseif (self::isOnMetaCourseForFacebook()) {
				$Data = true;
				self::$USER_ID = (int)Request::param('account');
			} else {
				$Data = false;
			}

			if ($Data === false)
				self::$USER_ID = -1;
		}

		return self::$USER_ID;
	}

	/**
	 * Get URL for a given training
	 * @param int $trainingID
	 * @return string 
	 */
	static public function getUrlFor($trainingID) {
		return self::$URL.self::idToUrl($trainingID);
	}

	/**
	 * Transform given ID to url
	 * @param int $id
	 * @return string 
	 */
	static private function idToUrl($id) {
		return base_convert((int)$id, 10, 35);
	}

	/**
	 * Transform given url to ID
	 * @param string $url
	 * @return int 
	 */
	static private function urlToId($url) {
		return (int)base_convert((string)$url, 35, 10);
	}
}
