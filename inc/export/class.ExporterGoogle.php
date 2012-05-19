<?php
/**
 * Exporter for: Google
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterGoogle extends ExporterSocialShare {
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
				Du wirst zur Seite von Google weitergeleitet.<br />
				Dort kannst du selbst bestimmen, welcher Text angezeigt wird.
			</small>');
	}

	/**
	 * Get link
	 * @return string 
	 */
	protected function getLink() {
		$URL = 'https://plus.google.com/share?url='.urlencode($this->getUrl()).'&h1=de';

		return '<a href="'.$URL.'" style="background-image:url(inc/export/icons/google.png);"><strong>Share +1</strong></a>';
	}
}