<?php
/**
 * This file contains class::Kml
 * @package Runalyze\Export\File
 */

namespace Runalyze\Export\File;

use League\Geotools\Coordinate\CoordinateInterface;
use Runalyze\Configuration;
use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;

/**
 * Create exporter for kml files
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\File
 */
class Kml extends AbstractFileExporter
{
    /** @var \SimpleXMLElement */
    protected $XML = null;

    /** @var string */
    protected $CurrentPath = '';

    /** @var int */
    protected $CurrentPauseIndex = 0;

    /** @var int */
    protected $CurrentPauseTime = 0;

    /** @var int */
    protected $NumPauses = 0;

    /** @var \Runalyze\Model\Route\Loop */
    protected $RouteLoop = null;

    /** @var \Runalyze\Model\Trackdata\Loop */
    protected $TrackdataLoop = null;

    /** @var \Runalyze\Model\Trackdata\Pauses */
    protected $Pauses = null;

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
        return 'kml';
    }

    /**
     * @return int
     */
    public function enum()
    {
        return Types::KML;
    }

    /**
     * Export
     */
    protected function createFile()
    {
        $this->XML = new \SimpleXMLElement($this->emptyXml());

        $this->setGeneralInfo();
        $this->setTrack();

        $this->FileContent = $this->XML->asXML();

        $this->formatFileContentAsXML();
    }

    /**
     * Set general info
     */
    protected function setGeneralInfo()
    {
        $this->XML->Folder->name = $this->nameForKml();
        $this->XML->Folder->Placemark->name = $this->nameForKml();
        $this->XML->Folder->Placemark->Style->geomColor = self::rgbToKmlColor( Configuration::ActivityView()->routeColor() );
    }

    /**
     * Set track
     */
    protected function setTrack()
    {
        $this->prepareLoop();

        while ($this->RouteLoop->nextStep()) {
            $this->TrackdataLoop->nextStep();

            if ($this->thereWasAPause()) {
                $this->setPauseToXml();
            }

            $this->addCoordinateToCurrentPath($this->RouteLoop->coordinate());
        }

        $this->addCurrentPathToXml();
    }

    /**
     * Prepare loop
     */
    protected function prepareLoop()
    {
        $this->RouteLoop = new Route\Loop($this->Context->route());
        $this->TrackdataLoop = new Trackdata\Loop($this->Context->trackdata());
        $this->Pauses = $this->Context->trackdata()->pauses();
        $this->NumPauses = $this->Pauses->num();

        if ($this->NumPauses > 0) {
            $this->CurrentPauseTime = $this->Pauses->at(0)->time();
        }
    }

    /**
     * @return bool
     */
    protected function thereWasAPause()
    {
        return ($this->CurrentPauseIndex < $this->NumPauses) && ($this->CurrentPauseTime < $this->TrackdataLoop->time());
    }

    /**
     * Set pause to xml
     */
    protected function setPauseToXml()
    {
        $this->addCurrentPathToXml();
        $this->CurrentPauseIndex++;

        if ($this->NumPauses > $this->CurrentPauseIndex) {
            $this->CurrentPauseTime = $this->Pauses->at($this->CurrentPauseIndex)->time();
        }
    }

    /**
     * @param \League\Geotools\Coordinate\CoordinateInterface $coordinate
     */
    protected function addCoordinateToCurrentPath(CoordinateInterface $coordinate)
    {
        if (abs($coordinate->getLatitude()) > 1e-5 || abs($coordinate->getLongitude()) > 1e-5) {
            $this->CurrentPath .= $coordinate->getLongitude().','.$coordinate->getLatitude().NL;
        }
    }

    /**
     * Add current path as coordinates child
     */
    protected function addCurrentPathToXml()
    {
        $LineString = $this->XML->Folder->Placemark->MultiGeometry->addChild('LineString');
        $LineString->addChild('coordinates', $this->CurrentPath);

        $this->CurrentPath = '';
    }

    /**
     * Get name for route in kml-file
     * @return string
     */
    protected function nameForKml()
    {
        return $this->Context->dataview()->date('Y-m-d').': '.$this->Context->dataview()->titleWithComment();
    }

    /**
     * Get empty xml
     * @return string
     */
    protected function emptyXml()
    {
        return
            '<kml xmlns="http://earth.google.com/kml/2.0">
  <Folder>
    <open>1</open>
    <name></name>
    <Placemark>
      <visibility>1</visibility>
      <name></name>
      <Style>
        <geomScale>2</geomScale>
        <geomColor></geomColor>
      </Style>
      <MultiGeometry></MultiGeometry>
    </Placemark>
  </Folder>
</kml>';
    }

    /**
     * Transform rgb-color (i.e. #FF7700 or FF7700) to abgr-color needed for kml
     * @param string $rgb
     * @return string
     */
    public static function rgbToKmlColor($rgb)
    {
        if (strlen($rgb) == 7) {
            $rgb = substr($rgb, 1);
        }

        return 'ff'.substr($rgb, 4, 2).substr($rgb, 2, 2).substr($rgb, 0, 2);
    }
}