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
	 * Icon class
	 * @return string
	 */
	static public function IconClass() {
		return 'fa-twitter color-twitter';
	}

	/**
	 * Display
	 */
	public function display() {
		$url = 'https://twitter.com/share?url='.$this->getPublicURL().'&text='.$this->getText().'&via=RunalyzeDE';

		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink($this->externalLink($url, __('Tweet!')) );
		$Linklist->display();

		echo HTML::info( __('You will be forwared to Twitter, where you can define which text shall be displayed.') );
	}
}