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
 * @deprecated since v3.1
 */
class SharedLinker {
	/**
	 * URL base for shared activities
	 * @var string
	 */
	public static $URL = 'shared/';

	/**
	 * User ID
	 * @var int
	 */
	public static $USER_ID = 0;

	/**
	 * Is the user on the shared page?
	 * @return boolean
	 */
	public static function isOnSharedPage() {
		return in_array('shared', explode('/', Request::Uri())) || in_array('athlete', explode('/', Request::Uri()));
	}

	/**
	 * Get link to a given training
	 * @param int $trainingID
	 * @return string
	 */
	public static function getToolbarLinkTo($trainingID) {
		return '<a href="'.self::getUrlFor($trainingID).'" target="_blank">'.Icon::$ATTACH.' '.__('Public link').'</a>';
	}

	/**
	 * Get link to a given training
	 * @param int $trainingID
	 * @param string $text [optional]
	 * @return string
	 */
	public static function getStandardLinkTo($trainingID, $text = null) {
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
	public static function getListLinkForCurrentUser($text = null) {
        if (is_null($text)) {
            $text = '<i class="fa fa-fw fa-id-card-o"></i>';
        }

		if (!Configuration::Privacy()->listIsPublic()) {
            return '<a class=window tab" href="settings?key=config_tab_general" '.Ajax::tooltip('', __('You can activate your public athlete page at <em>Configuration->Privacy</em>'), false, true).'>'.$text.'</a>';
		} else {
            return '<a href="athlete/' . SessionAccountHandler::getUsername() . '" target="_blank" ' . Ajax::tooltip('', __('Your public athlete page'), false, true) . '>' . $text . '</a>';
        }
    }

	/**
	 * Get training ID from request
	 * @return int
	 */
	public static function getTrainingId() {
		return self::urlToId( Request::param('url') );
	}

	/**
	 * Get user ID
	 * @return int
	 */
	public static function getUserId() {
		if (self::$USER_ID <= 0) {
			if (strlen(Request::param('user')) > 0) {
                $Data = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `username`='.DB::getInstance()->escape(Request::param('user')).' LIMIT 1')->fetch();
				self::$USER_ID = $Data['id'];
			} elseif (strlen(Request::param('url')) > 0) {
				DB::getInstance()->stopAddingAccountID();
				$Data = DB::getInstance()->query('SELECT `accountid` FROM `'.PREFIX.'training` WHERE id="'.self::getTrainingId().'" LIMIT 1')->fetch();
				DB::getInstance()->startAddingAccountID();

				self::$USER_ID = $Data['accountid'];
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
	public static function getUrlFor($trainingID) {
		return self::$URL.self::idToUrl($trainingID);
	}

	/**
	 * Transform given ID to url
	 * @param int $id
	 * @return string
	 */
	private static function idToUrl($id) {
		return base_convert((int)$id, 10, 35);
	}

	/**
	 * Transform given url to ID
	 * @param string $url
	 * @return int
	 */
	private static function urlToId($url) {
		return (int)base_convert((string)$url, 35, 10);
	}
}
