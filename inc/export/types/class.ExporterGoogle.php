<?php
/**
 * This file contains class::ExporterGoogle
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: Google+
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterGoogle extends ExporterAbstractSocialShare {
	/**
	 * Display
	 */
	public function display() {
		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink( $this->getLink() );
		$Linklist->display();

		echo HTML::info('
			Du wirst zur Seite von Google weitergeleitet.<br />
			Dort kannst du selbst bestimmen, welcher Text angezeigt wird.
		');

		$this->throwLinkErrorForLocalhost();
	}

	/**
	 * Get link
	 * @return string 
	 */
	protected function getLink() {
		$URL = 'https://plus.google.com/share?url='.urlencode($this->Training->Linker()->publicUrl()).'&h1=de';

		return '<a href="'.$URL.'" target="_blank" style="background-image:url(inc/export/icons/google.png);"><strong>Share +1</strong></a>';
	}
}