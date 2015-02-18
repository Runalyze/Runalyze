<?php
/**
 * This file contains class::ExporterGPX
 * @package Runalyze\Export\Types
 */

use Runalyze\Model\Trackdata;
use Runalyze\Model\Route;

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
		if (!$this->Context->hasRoute() || !$this->Context->route()->hasPositionData()) {
			$this->addError( __('The training does not contain gps-data and cannot be saved as gpx-file.') );

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
		$Starttime = $this->Context->activity()->timestamp();

		$Trackdata = new Trackdata\Loop($this->Context->trackdata());
		$Route = new Route\Loop($this->Context->route());

		$hasElevation = $this->Context->route()->hasOriginalElevations();
		$hasHeartrate = $this->Context->trackdata()->has(Trackdata\Object::HEARTRATE);

		while ($Trackdata->nextStep()) {
			$Route->nextStep();

			$Trackpoint = $this->Track->addChild('trkpt');
			$Trackpoint->addAttribute('lat', $Route->latitude());
			$Trackpoint->addAttribute('lon', $Route->longitude());
			$Trackpoint->addChild('time', $this->timeToString($Starttime + $Trackdata->time()));

			if ($hasElevation) {
				$Trackpoint->addChild('ele', $Route->current(Route\Object::ELEVATIONS_ORIGINAL));
			}

			if ($hasHeartrate) {
                $ext = $Trackpoint->addChild('extensions');
                $tpe = $ext->addChild('gpxtpx:TrackPointExtension','','http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
                $tpe->addChild('gpxtpx:hr',  $Trackdata->current(Trackdata\Object::HEARTRATE));
            }
		}
	}

	/**
	 * Get empty xml
	 * @return string 
	 */
	protected function getEmptyXml() {
		return
'<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1" creator="Runalyze"
  xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd"
  xmlns="http://www.topografix.com/GPX/1/1"
  xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1"
  xmlns:gpxx="http://www.garmin.com/xmlschemas/GpxExtensions/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
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