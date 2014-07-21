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
	 * Icon class
	 * @return string
	 */
	static public function IconClass() {
		return 'fa-google-plus color-google-plus';
	}

	/**
	 * Display
	 */
	public function display() {
		if (!$this->Training->isPublic()) {
			echo HTML::error( __('This training is private and can\'t be shared.') );
			return;
		}

		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink( $this->getLink() );
		$Linklist->display();

		echo HTML::info( __('You will be forwared to Google+, where you can define which text shall be displayed.') );

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