<?php
/**
 * This file contains class::ExporterGoogle
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: Google+
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Export\Types
 */
class ExporterGoogle extends ExporterAbstractSocialShare {
    
    const EXPORTER_TYPE = 2;
    
	/**
	 * Icon class
	 * @return string
	 */
	public static function getIconClass() {
		return 'fa-google-plus color-google-plus';
	}
	
	
	public function getUrl() {
	    $url = 'https://plus.google.com/share?url='.urlencode($this->getPublicURL()).'&h1=de';
	    return $url;
	}
	
	public function getActionText() {
	    return __('Share +1');
	}
	
	public function getName() {
	    return __('Google+');
	}
	
	public function getInfoText() {
	    return __('You will be forwared to Google+, where you can define which text shall be displayed.');
	}

}