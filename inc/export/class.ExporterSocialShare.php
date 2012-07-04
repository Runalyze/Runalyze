<?php
/**
 * Exporter for social sharing
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
abstract class ExporterSocialShare extends Exporter {
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
			$Text .= $this->Training->getDistanceString().' ';
			$Text .= $this->Training->getTitle().' in ';
			$Text .= $this->Training->getTimeString().' ';
			$Text .= '('.$this->Training->getSpeedString().')';
		} else {
			$Text .= $this->Training->getTimeString().' ';
			$Text .= $this->Training->getTitle();
		}


		if (strlen($this->Training->get('comment')) > 0)
			$Text .= ' - '.$this->Training->get('comment');

		return strip_tags(str_replace('&nbsp;', '', $Text));
	}

	/**
	 * Get URL to share
	 * @return string 
	 */
	final protected function getUrl() {
		if ($this->Training->isPublic())
			return System::getFullDomain().SharedLinker::getUrlFor($this->Training->id());

		return '';
	}
}