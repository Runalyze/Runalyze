<?php
/**
 * This file contains class::Kml
 * @package Runalyze\Export\File
 */

namespace Runalyze\Export\File;

use League\Geotools\Geohash\Geohash;
use Runalyze\Configuration;

/**
 * Create exporter for kml files
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\File
 */
class Kml extends AbstractFileExporter
{
    /** @var SimpleXMLElement */
    private $XML = null;

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
        $Track = '';

        $Loop = new \Runalyze\Model\Route\Loop($this->Context->route());

        while ($Loop->nextStep()) {
            // TODO: start a new line after a pause
            $coordinate = (new Geohash())->decode($Loop->geohash())->getCoordinate();
            if (abs($coordinate->getLatitude()) > 1e-5 || abs($coordinate->getLongitude()) > 1e-5) {
                $Track .= $coordinate->getLongitude().','.$coordinate->getLatitude().NL;
            }
        }

        $this->XML->Folder->Placemark->LineString->addChild('coordinates', $Track);
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
      <LineString>
      </LineString>
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