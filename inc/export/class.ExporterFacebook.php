<?php
/**
 * Exporter for: Facebook
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterFacebook extends ExporterSocialShare {
	/**
	 * APP-ID
	 * @var string 
	 */
	public static $APP_ID = '473795412675725';

	/**
	 * Is this exporter without a file?
	 * @return boolean 
	 */
	public static function isWithoutFile() {
		return true;
	}

	/**
	 * Set file content
	 */
	protected function setFileContent() {
		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink( $this->getLink() );
		$Linklist->display();

		echo HTML::info('
			<small>
				Du wirst zur Seite von Facebook weitergeleitet.<br />
				Dort kannst du selbst bestimmen, welcher Text angezeigt wird.
			</small>');
	}

	/**
	 * Get link
	 * @return string 
	 */
	protected function getLink() {
		$url   = urlencode($this->getUrl());
		$title = urlencode($this->Training->getTitle().' am '.$this->Training->getDate(false).' - Trainingsansicht');
		$text  = urlencode($this->getText());
		$image = 'http://runalyze.de/wp-content/uploads/Account.png';

		$FbUrl = 'https://www.facebook.com/dialog/feed?app_id='.self::$APP_ID.'&link='.$url.'&picture='.$image.'&name='.$title.'&caption='.$url.'&description='.$text.'&redirect_uri=http://www.facebook.com';
		//$FbUrl = 'https://facebook.com/sharer.php?s=100&amp;p[url]='.$url.'&amp;p[title]='.$title.'&amp;p[summary]='.$text.'&amp;p[images][0]='.$image;

		return '<a href="'.$FbUrl.'" target="_blank" style="background-image:url(inc/export/icons/facebook.png);"><strong>Teilen!</strong></a>';
	}
}