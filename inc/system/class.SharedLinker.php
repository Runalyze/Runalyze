<?php
/**
 * This file contains class::SharedLinker
 * @package Runalyze\System
 */
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
		return in_array('shared', explode('/', Request::Uri()));
	}

	/**
	 * Get link to a given training
	 * @param int $trainingID
	 * @return string
	 */
	static public function getToolbarLinkTo($trainingID) {
		return '<a class="labeledLink" href="'.self::getUrlFor($trainingID).'" target="_blank" title="Permalink zum Training">'.Icon::$ATTACH.' &ouml;ffentlicher Link</a>';
	}

	/**
	 * Get link to a given training
	 * @param int $trainingID
	 * @return string
	 */
	static public function getStandardLinkTo($trainingID) {
		return '<a href="'.self::getUrlFor($trainingID).'" target="_blank" title="Permalink zum Training">'.Icon::$ATTACH.'</a>';
	}

	/**
	 * Get link to shared list for current user
	 * @return string 
	 */
	static public function getListLinkForCurrentUser() {
		if (!CONF_TRAINING_LIST_PUBLIC)
			return '';

		return '<a href="shared/'.SessionAccountHandler::getUsername().'/" title="&Ouml;ffentliche Trainingsliste" target="_blank">'.Icon::$ATTACH.'</a>';
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
				$Data = Mysql::getInstance()->untouchedFetch('SELECT `accountid` FROM `'.PREFIX.'training` WHERE id="'.self::getTrainingId().'" LIMIT 1');
				self::$USER_ID = $Data['accountid'];
			}
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