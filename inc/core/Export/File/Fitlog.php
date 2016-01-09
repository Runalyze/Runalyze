<?php
/**
 * This file contains class::Fitlog
 * @package Runalyze\Export\File
 */

namespace Runalyze\Export\File;

use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;
use SessionAccountHandler;

/**
 * Create exporter for fitlog files
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\File
 */
class Fitlog extends AbstractFileExporter
{
    /** @var \SimpleXMLElement */
    protected $XML = null;

    /** @var \SimpleXMLElement */
    protected $Activity = null;

    /**
     * @return bool
     */
    public function isPossible()
    {
        return true;
    }

    /**
     * Get extension
     * @return string
     */
    public function extension()
    {
        return 'fitlog';
    }

    /**
     * @return int
     */
    public function enum()
    {
        return Types::FITLOG;
    }

    /**
     * Export
     */
    protected function createFile()
    {
        $this->XML = new \SimpleXMLElement($this->emptyXml());
        $this->Activity = $this->XML->AthleteLog->Activity;

        $this->setGeneralInfo();
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
        return date("c", (int)$time);
    }

    /**
     * Set general info
     */
    protected function setGeneralInfo()
    {
        if (strlen(SessionAccountHandler::getName()) > 0) {
            $this->XML->AthleteLog->Athlete->addAttribute('Name', SessionAccountHandler::getName());
        }

        $this->Activity->addAttribute('StartTime', $this->timeToString($this->Context->activity()->timestamp()));

        $this->Activity->Duration->addAttribute('TotalSeconds', (int)$this->Context->activity()->duration());
        $this->Activity->Distance->addAttribute('TotalMeters', 1000*$this->Context->activity()->distance());
        $this->Activity->Calories->addAttribute('TotalCal', $this->Context->activity()->calories());
    }

    /**
     * Add track to xml
     */
    protected function setTrack()
    {
        if (!$this->Context->hasTrackdata()) {
            return;
        }

        $Starttime = $this->Context->activity()->timestamp();
        $Trackdata = new Trackdata\Loop($this->Context->trackdata());
        $Route = ($this->Context->hasRoute() && $this->Context->route()->hasPositionData())
            ? new Route\Loop($this->Context->route()) : null;

        $hasHeartrate = $this->Context->trackdata()->has(Trackdata\Entity::HEARTRATE);

        $Track = $this->Activity->addChild('Track');
        $Track->addAttribute('StartTime', $this->timeToString($Starttime));

        while ($Trackdata->nextStep()) {
            $Point = $Track->addChild('pt');
            $Point->addAttribute('tm', $Trackdata->time());

            if (null !== $Route) {
                $Route->nextStep();
                $Point->addAttribute('lat', $Route->latitude());
                $Point->addAttribute('lon', $Route->longitude());
                $Point->addAttribute('ele', $Route->current(Route\Entity::ELEVATIONS_ORIGINAL));
            }

            if ($hasHeartrate) {
                $Point->addAttribute('hr', $Trackdata->current(Trackdata\Entity::HEARTRATE));
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
            '<FitnessWorkbook xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.zonefivesoftware.com/xmlschemas/FitnessLogbook/v2">
 <AthleteLog>
  <Athlete />
  <Activity>
   <Duration />
   <Distance />
   <Calories />
   <Category />
   <Location />
  </Activity>
 </AthleteLog>
</FitnessWorkbook>';
    }
}