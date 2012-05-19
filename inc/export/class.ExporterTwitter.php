<?php
/**
 * Exporter for: Twitter
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterTwitter extends ExporterSocialShare {
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
				Du wirst zur Seite von Twitter weitergeleitet.<br />
				Dort kannst du selbst bestimmen, welcher Text angezeigt wird.
			</small>');
	}

	/**
	 * Get link
	 * @return string 
	 */
	protected function getLink() {
		$URL = 'https://twitter.com/share?url='.$this->getUrl().'&text='.$this->getText().'&via=RunalyzeDE';

		return '<a href="'.$URL.'" style="background-image:url(inc/export/icons/twitter.png);"><strong>Twittern!</strong></a>';
	}
}