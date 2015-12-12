<?php
/**
 * This file contains class::ExporterFacebook
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: Facebook
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterFacebook extends ExporterAbstractSocialShare {
    
    const EXPORTER_TYPE = 2;
    
	/**
	 * Icon class
	 * @return string
	 */
	public static function getIconClass() {
		return 'fa-facebook color-facebook';
	}

	/**
	 * APP-ID
	 * @var string 
	 */
	public static $APP_ID = '473795412675725';

	public function getUrl() {
		// TODO
		// check: on runalyze.com
		// access_token, see http://www.espend.de/artikel/facebook-api-oauth-access-token-generieren.html
		// publish, call https://graph.facebook.com/me/fitness.runs?access_token=XXX&method=POST&course=...
		// for testing, see https://developers.facebook.com/tools/explorer/473795412675725/?path=me%2Ffitness.runs&method=POST
		// @see https://developers.facebook.com/docs/reference/opengraph/action-type/fitness.runs
		// @see https://developers.facebook.com/docs/reference/opengraph/object-type/fitness.course

		$url   = urlencode($this->getPublicURL());
		$title = urlencode($this->Context->dataview()->titleWithComment());
		$text  = urlencode($this->getText());
		$image = System::getFullDomain(true).'/web/assets/images/runalyze.png';

		return 'https://www.facebook.com/dialog/feed?app_id='.self::$APP_ID.'&link='.$url.'&picture='.$image.'&name='.$title.'&caption='.$url.'&description='.$text.'&redirect_uri=http://www.facebook.com';
		//$FbUrl = 'https://facebook.com/sharer.php?s=100&amp;p[url]='.$url.'&amp;p[title]='.$title.'&amp;p[summary]='.$text.'&amp;p[images][0]='.$image;
	}
	
	public function getActionText() {
	    return __('Share!');
	}
	
	public function getName() {
	    return __('Facebook');
	}
	
	public function getInfoText() {
	    return __('You will be forwarded to Facebook, where you can define which text shall be displayed.');
	}

	/**
	 * Get meta title
	 * @return string
	 */
	public function metaTitle() {
		return $this->getText();
	}
}
