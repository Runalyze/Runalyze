<?php
/**
 * This file contains class::ExporterAbstractSocialShare
 * @package Runalyze\Export\Types
 */

use Runalyze\View\Activity\Linker;

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

		if ($this->Context->activity()->distance() > 0) {
			$Text .= $this->Context->dataview()->distance().' ';
			$Text .= $this->Context->dataview()->titleByTypeOrSport().__(' in ');
			$Text .= $this->Context->dataview()->duration()->string().' ';
			$Text .= '('.$this->Context->dataview()->pace()->valueWithAppendix().')';
		} else {
			$Text .= $this->Context->dataview()->duration()->string().' ';
			$Text .= $this->Context->dataview()->titleByTypeOrSport();
		}


		if ($this->Context->activity()->comment() != '') {
			$Text .= ' - '.$this->Context->activity()->comment();
		}

		return strip_tags(str_replace('&nbsp;', '', $Text));
	}

	/**
	 * @return string
	 */
	final protected function getPublicURL() {
		$Linker = new Linker($this->Context->activity());
		return $Linker->publicUrl();
	}

	/**
	 * @param string $url
	 * @param string $text
	 * @return string
	 */
	final protected function externalLink($url, $text) {
		return '<a href="'.$url.'" target="_blank" style="display:block!important"><i class="fa '.static::IconClass().'"></i> <strong>'.$text.'</strong></a>';
	}

	/**
	 * Throw error for localhost
	 */
	final protected function throwLinkErrorForLocalhost() {
		if (System::isAtLocalhost()) {
			echo HTML::error(
					__('Runalyze is running on a local server.').' '.
					__('Linking your activity in a social network does not make sense - nobody will be able to see your activity.')
			);
		}
	}
}