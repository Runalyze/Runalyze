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
	 * Type
	 * @return enum
	 */
	static public function Type() {
		return ExporterType::Social;
	}

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
		$Text  = __('I did sport: ');

		if ($this->Training->hasDistance()) {
			$Text .= $this->Training->DataView()->getDistanceString().' ';
			$Text .= $this->Training->DataView()->getTitle().__(' in ');
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
			echo HTML::error(
					__('Runalyze is running on a local server.').' '.
					__('Linking your training in a social network does not make sense - nobody will be able to see your training.')
			);
	}
}