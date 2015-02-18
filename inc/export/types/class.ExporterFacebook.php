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
	/**
	 * Icon class
	 * @return string
	 */
	static public function IconClass() {
		return 'fa-facebook color-facebook';
	}

	/**
	 * APP-ID
	 * @var string 
	 */
	public static $APP_ID = '473795412675725';

	/**
	 * Display
	 */
	public function display() {
		if (!$this->Context->activity()->isPublic()) {
			echo HTML::error( __('This training is private and cannot be shared.') );
			return;
		}

		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink( $this->externalLink($this->getUrl(), __('Share!')) );
		$Linklist->display();

		echo HTML::info( __('You will be forwared to Facebook, where you can define which text shall be displayed.') );

		$this->throwLinkErrorForLocalhost();
	}

	/**
	 * Get link
	 * @return string 
	 */
	protected function getUrl() {
		// TODO
		// Wenn v1.3 online ist:
		// check: on user.runalyze.de/user.runalyze.com
		// access_token, see http://www.espend.de/artikel/facebook-api-oauth-access-token-generieren.html
		// publish, call https://graph.facebook.com/me/fitness.runs?access_token=XXX&method=POST&course=...
		// for testing, see https://developers.facebook.com/tools/explorer/473795412675725/?path=me%2Ffitness.runs&method=POST
		// @see https://developers.facebook.com/docs/reference/opengraph/action-type/fitness.runs
		// @see https://developers.facebook.com/docs/reference/opengraph/object-type/fitness.course

		$url   = urlencode($this->getPublicURL());
		$title = urlencode($this->Context->dataview()->titleWithComment());
		$text  = urlencode($this->getText());
		$image = 'http://runalyze.de/wp-content/uploads/Account.png';

		return 'https://www.facebook.com/dialog/feed?app_id='.self::$APP_ID.'&link='.$url.'&picture='.$image.'&name='.$title.'&caption='.$url.'&description='.$text.'&redirect_uri=http://www.facebook.com';
		//$FbUrl = 'https://facebook.com/sharer.php?s=100&amp;p[url]='.$url.'&amp;p[title]='.$title.'&amp;p[summary]='.$text.'&amp;p[images][0]='.$image;
	}

	/**
	 * Get meta title
	 * @return string
	 */
	public function metaTitle() {
		return $this->getText();
	}
}