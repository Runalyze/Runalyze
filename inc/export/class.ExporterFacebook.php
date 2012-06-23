<?php
/**
 * Exporter for: Facebook
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterFacebook extends ExporterSocialShare {
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

		$URL = 'https://facebook.com/sharer.php?s=100&amp;p[url]='.$url.'&amp;p[title]='.$title.'&amp;p[summary]='.$text.'&amp;p[images][0]='.$image;

		return '<a href="'.$URL.'" target="_blank" style="background-image:url(inc/export/icons/facebook.png);"><strong>Teilen!</strong></a>';
	}
}