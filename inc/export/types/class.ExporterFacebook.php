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
	 * APP-ID
	 * @var string 
	 */
	public static $APP_ID = '473795412675725';

	/**
	 * Display
	 */
	public function display() {
		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink( $this->getLink() );
		$Linklist->display();

		echo HTML::info('
				Du wirst zur Seite von Facebook weitergeleitet.<br />
				Dort kannst du selbst bestimmen, welcher Text angezeigt wird.
		');

		$this->throwLinkErrorForLocalhost();
	}

	/**
	 * Get link
	 * @return string 
	 */
	protected function getLink() {
		$url   = urlencode($this->Training->Linker()->publicUrl());
		$title = urlencode($this->Training->DataView()->getTitle().' am '.$this->Training->DataView()->getDate(false).' - Trainingsansicht');
		$text  = urlencode($this->getText());
		$image = 'http://runalyze.de/wp-content/uploads/Account.png';

		$FbUrl = 'https://www.facebook.com/dialog/feed?app_id='.self::$APP_ID.'&link='.$url.'&picture='.$image.'&name='.$title.'&caption='.$url.'&description='.$text.'&redirect_uri=http://www.facebook.com';
		//$FbUrl = 'https://facebook.com/sharer.php?s=100&amp;p[url]='.$url.'&amp;p[title]='.$title.'&amp;p[summary]='.$text.'&amp;p[images][0]='.$image;

		return '<a href="'.$FbUrl.'" target="_blank" style="background-image:url(inc/export/icons/facebook.png);"><strong>Teilen!</strong></a>';
	}
}