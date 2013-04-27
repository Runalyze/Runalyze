<?php
/**
 * This file contains class::ExporterAbstractSocialShare
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: social sharing
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
abstract class ExporterAbstractSocialShare extends ExporterAbstract {
	/**
	 * Get extension
	 * @return string 
	 */
	final protected function getExtension() {
		return 'none';
	}

	/**
	 * Get text for sharing 
	 */
	final protected function getText() {
		$Text  = 'Ich habe Sport gemacht: ';

		if ($this->Training->hasDistance()) {
			$Text .= $this->Training->DataView()->getDistanceString().' ';
			$Text .= $this->Training->DataView()->getTitle().' in ';
			$Text .= $this->Training->DataView()->getTimeString().' ';
			$Text .= '('.$this->Training->DataView()->getSpeedString().')';
		} else {
			$Text .= $this->Training->DataView()->getTimeString().' ';
			$Text .= $this->Training->DataView()->getTitle();
		}


		if (strlen($this->Training->getComment()) > 0)
			$Text .= ' - '.$this->Training->getComment();

		return strip_tags(str_replace('&nbsp;', '', $Text));
	}

	/**
	 * Throw error for localhost
	 */
	final protected function throwLinkErrorForLocalhost() {
		if (System::isAtLocalhost())
			echo HTML::error('
				Runalyze l&auml;uft auf einem lokalen Server.
				Eine Verlinkung in sozialen Netzwerken macht eigentlich keinen Sinn.
				Niemand wird den Link aufrufen k&ouml;nnen.
			');
	}
}