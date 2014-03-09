<?php
/**
 * This file contains class::ExporterTwitter
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: Twitter
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterTwitter extends ExporterAbstractSocialShare {
	/**
	 * Display
	 */
	public function display() {
		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink( $this->getLink() );
		$Linklist->display();

		echo HTML::info('
			Du wirst zur Seite von Twitter weitergeleitet.<br>
			Dort kannst du selbst bestimmen, welcher Text angezeigt wird.
		');
	}

	/**
	 * Get link
	 * @return string 
	 */
	protected function getLink() {
		$URL = 'https://twitter.com/share?url='.$this->Training->Linker()->publicUrl().'&text='.$this->getText().'&via=RunalyzeDE';

		return '<a href="'.$URL.'" target="_blank" style="background-image:url(inc/export/icons/twitter.png);"><strong>Twittern!</strong></a>';
	}
}