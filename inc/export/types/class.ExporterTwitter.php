<?php
/**
 * This file contains class::ExporterTwitter
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: Twitter
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Export\Types
 */
class ExporterTwitter extends ExporterAbstractSocialShare {
    
	const EXPORTER_TYPE = 2;
    
	/**
	 * Icon class
	 * @return string
	 */
	public static function getIconClass() {
		return 'fa-twitter color-twitter';
	}
	
	
	public function getUrl() {
	    $url = 'https://twitter.com/share?url='.$this->getPublicURL().'&text='.$this->getText().'&via=RunalyzeDE';
	    return $url;
	}
	
	public function getActionText() {
	    return __('Tweet!');
	}
	
	public function getName() {
	    return __('Twitter');
	}
	
	public function getInfoText() {
	    return __('You will be forwared to Twitter, where you can define which text shall be displayed.');
	}

}
