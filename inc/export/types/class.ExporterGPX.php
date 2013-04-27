<?php
/**
 * This file contains class::ExporterGPX
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: GPX
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterGPX extends ExporterAbstractFile {
	/**
	 * XML construct
	 * @var SimpleXMLElement
	 */
	private $XML = null;

	/**
	 * Track-part of XML construct
	 * @var SimpleXMLElement 
	 */
	private $Track = null;

	/**
	 * Get extension
	 * @return string 
	 */
	protected function getExtension() {
		return 'gpx';
	}

	/**
	 * Set file content
	 */
	protected function setFileContent() {
		if (!$this->Training->GpsData()->hasPositionData()) {
			$this->addError('Das Training enth&auml;lt keine GPS-Daten und kann daher nicht als *.gpx-Datei gespeichert werden.');

			return;
		}

		$this->XML   = new SimpleXMLElement($this->getEmptyXml());
		$this->Track = $this->XML->trk->trkseg;

		$this->setTrack();	

		$this->FileContent = $this->XML->asXML();

		$this->formatFileContentAsXML();
	}

	/**
	 * Get string for timestamp in xml
	 * @param int $time
	 * @return string 
	 */
	final protected function timeToString($time) {
		return date("c", $time);
	}

	/**
	 * Add track to xml 
	 */
	protected function setTrack() {
		$Starttime = $this->Training->get('time');
		$GPS = $this->Training->GpsData();
		$GPS->startLoop();

		while ($GPS->nextStep()) {
			$Trackpoint = $this->Track->addChild('trkpt');
			$Trackpoint->addAttribute('lat', $GPS->getLatitude());
			$Trackpoint->addAttribute('lon', $GPS->getLongitude());

			$Trackpoint->addChild('ele', $GPS->getElevation());
			$Trackpoint->addChild('time', $this->timeToString($Starttime + $GPS->getTime()));
		}
	}

	/**
	 * Get empty xml
	 * @return string 
	 */
	protected function getEmptyXml() {
		return
'<gpx xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" version="1.1" creator="SportTracks 2.1" xmlns="http://www.topografix.com/GPX/1/1">
 <metadata />
 <trk>
  <name />
  <cmt />
  <trkseg>
  </trkseg>
 </trk>
</gpx>';
	}
}