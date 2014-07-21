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
		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink( $this->getLink() );
		$Linklist->display();

		echo HTML::info( __('You will be forwared to Twitter, where you can define which text shall be displayed.') );
	}

	/**
	 * Get link
	 * @return string 
	 */
	protected function getLink() {
		$URL = 'https://twitter.com/share?url='.$this->Training->Linker()->publicUrl().'&text='.$this->getText().'&via=RunalyzeDE';

		return '<a href="'.$URL.'" target="_blank" style="background-image:url(inc/export/icons/twitter.png);"><strong>'.__('Tweet!').'</strong></a>';
	}
}