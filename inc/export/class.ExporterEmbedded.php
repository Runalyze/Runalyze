<?php
/**
 * Exporter for embedded trainings
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
abstract class ExporterEmbedded extends Exporter {
	/**
	 * Is this exporter without a file?
	 * @return boolean 
	 */
	public static function isWithoutFile() {
		return true;
	}

	/**
	 * Get extension
	 * @return string 
	 */
	final protected function getExtension() {
		return 'none';
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