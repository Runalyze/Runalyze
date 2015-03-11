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
		if (!$this->Context->activity()->isPublic()) {
			echo HTML::error( __('This training is private and cannot be shared.') );
			return;
		}

		$url = 'https://plus.google.com/share?url='.urlencode($this->getPublicURL()).'&h1=de';

		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink($this->externalLink($url, __('Share +1')) );
		$Linklist->display();

		echo HTML::info( __('You will be forwared to Google+, where you can define which text shall be displayed.') );

		$this->throwLinkErrorForLocalhost();
	}
}