<?php
/**
 * Class for handling links to shared activities
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class SharedLinker {
	/**
	 * URL base for shared activities
	 * @var string
	 */
	static public $URL = 'shared/';

	/**
	 * Private constructor 
	 */
	private function __construct() {}

	/**
	 * Is the user on the shared page?
	 * @return boolean
	 */
	static public function isOnSharedPage() {
		return Request::CurrentFolder() == 'shared';
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
	 * Get training ID from request
	 * @return int
	 */
	static public function getTrainingId() {
		return self::urlToId( Request::param('url') );
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