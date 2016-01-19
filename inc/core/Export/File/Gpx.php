<?php
/**
 * This file contains class::Gpx
 * @package Runalyze\Export\File
 */

namespace Runalyze\Export\File;

use Runalyze\Configuration;
use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;

/**
 * Create exporter for gpx files
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\File
 */
class Gpx extends AbstractFileExporter
{
    /** @var \SimpleXMLElement */
    protected $XML = null;

    /** @var \SimpleXMLElement */
    protected $Track = null;

    /**
     * @return bool
     */
    public function isPossible()
    {
        return ($this->Context->hasRoute() && $this->Context->route()->hasPositionData());
    }

    /**
     * Get extension
     * @return string
     */
    public function extension()
    {
        return 'gpx';
    }

    /**
     * @return int
     */
    public function enum()
    {
        return Types::GPX;
    }

    /**
     * Export
     */
    protected function createFile()
    {
        $this->XML   = new \SimpleXMLElement($this->emptyXml());
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
    final protected function timeToString($time)
    {
        return date("c", $time);
    }

    /**
     * Add track to xml
     */
    protected function setTrack()
    {
        $Starttime = $this->Context->activity()->timestamp();

        $Trackdata = new Trackdata\Loop($this->Context->trackdata());
        $Route = new Route\Loop($this->Context->route());

        $hasElevation = $this->Context->route()->hasOriginalElevations();
        $hasHeartrate = $this->Context->trackdata()->has(Trackdata\Entity::HEARTRATE);

        while ($Trackdata->nextStep()) {
            $Route->nextStep();

            $Trackpoint = $this->Track->addChild('trkpt');
            $Trackpoint->addAttribute('lat', $Route->latitude());
            $Trackpoint->addAttribute('lon', $Route->longitude());
            $Trackpoint->addChild('time', $this->timeToString($Starttime + $Trackdata->time()));

            if ($hasElevation) {
                $Trackpoint->addChild('ele', $Route->current(Route\Entity::ELEVATIONS_ORIGINAL));
            }

            if ($hasHeartrate) {
                $ext = $Trackpoint->addChild('extensions');
                $tpe = $ext->addChild('gpxtpx:TrackPointExtension','','http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
                $tpe->addChild('gpxtpx:hr',  $Trackdata->current(Trackdata\Entity::HEARTRATE));
            }
        }
    }

    /**
     * Get empty xml
     * @return string
     */
    protected function emptyXml()
    {
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