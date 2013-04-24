<?php
ImporterWindowTabUpload::addInfo('HRM-Import: aus einer hrm- und gpx-Datei kann mit
	<a href="http://polar2tcx.runalyze.de" title="Polar-Daten in *.tcx-Datei umwandeln">polar2tcx.runalyze.de</a>
	eine tcx-Datei erstellt werden, die hier importiert werden kann.');
/**
 * This file contains class::ImporterFiletypeTCX
 * @package Runalyze\Importer\Filetype
 */
/**
 * Importer: *.tcx
 * 
 * Files of *.tcx have to be Garmin tcx-files.
 * This importer only runs the tcx parser
 *
 * @author Hannes Christiansen
 * @package Runalyze\Importer\Filetype
 */
class ImporterFiletypeTCX extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserTCXMultiple($String);
	}
}