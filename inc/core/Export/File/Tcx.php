<?php
/**
 * This file contains class::Tcx
 * @package Runalyze\Export\File
 */

namespace Runalyze\Export\File;

use Runalyze\Configuration;
use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;

/**
 * Create exporter for tcx files
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\File
 */
class Tcx extends AbstractFileExporter
{
    /** @var SimpleXMLElement */
    private $XML = null;

    /** @var SimpleXMLElement */
    private $Activity = null;

    /**
     * @return bool
     */
    public function isPossible()
    {
        return $this->Context->hasTrackdata();
    }

    /**
     * Get extension
     * @return string
     */
    public function extension()
    {
        return 'tcx';
    }

    /**
     * @return int
     */
    public function enum()
    {
        return Types::TCX;
    }

    /**
     * Export
     */
    protected function createFile()
    {
        $this->XML = new \SimpleXMLElement($this->emptyXml());
        $this->Activity = $this->XML->Activities->Activity;

        $this->setGeneralInfo();
        $this->setLaps();

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
     * Set general info
     */
    protected function setGeneralInfo()
    {
        $this->Activity->addChild('Id', $this->timeToString($this->Context->activity()->timestamp()));
    }

    /**
     * Add all laps to xml
     */
    protected function setLaps()
    {
        $Starttime = $this->Context->activity()->timestamp();

        $Loop = new Trackdata\Loop($this->Context->trackdata());

        while (!$Loop->isAtEnd()) {
            $Loop->nextKilometer();
            $TimeInSeconds = $Loop->difference(Trackdata\Entity::TIME);

            $Lap = $this->Activity->addChild('Lap');
            $Lap->addAttribute('StartTime', $this->timeToString($Starttime + $Loop->time() - $TimeInSeconds));
            $Lap->addChild('TotalTimeSeconds', $TimeInSeconds);
            $Lap->addChild('DistanceMeters', 1000*$Loop->difference(Trackdata\Entity::DISTANCE));

            if ($this->Context->trackdata()->has(Trackdata\Entity::HEARTRATE)) {
                $AvgBpm = $Lap->addChild('AverageHeartRateBpm');
                $AvgBpm->addChild('Value', $Loop->average(Trackdata\Entity::HEARTRATE));
                $MaxBpm = $Lap->addChild('MaximumHeartRateBpm');
                $MaxBpm->addChild('Value', $Loop->max(Trackdata\Entity::HEARTRATE));
            }

            $Lap->addChild('Intensity', 'Active');
            $Lap->addChild('TriggerMethod', 'Distance');
            $Lap->addChild('Track');

            // TODO: Calories?
        }

        $this->setTrack();
    }

    /**
     * Add track to all laps to xml
     */
    protected function setTrack()
    {
        $Starttime = $this->Context->activity()->timestamp();

        $Trackdata = new Trackdata\Loop($this->Context->trackdata());


        $hasHeartrate = $this->Context->trackdata()->has(Trackdata\Entity::HEARTRATE);
        $hasRoute = $this->Context->hasRoute();

        if($hasRoute) {
            $Route = new Route\Loop($this->Context->route());
            $hasElevation = $this->Context->route()->hasOriginalElevations();
        }

        while ($Trackdata->nextStep()) {
            if($hasRoute)
                $Route->nextStep();

            if ($this->Activity->Lap[(int)floor($Trackdata->distance())]) {
                $Trackpoint = $this->Activity->Lap[(int)floor($Trackdata->distance())]->Track->addChild('Trackpoint');
                $Trackpoint->addChild('Time', $this->timeToString($Starttime + $Trackdata->time()));
                
                if($this->Context->trackdata()->has(Trackdata\Entity::CADENCE))
                    $Trackpoint->addChild('Cadence', $Trackdata->current (Trackdata\Entity::CADENCE));

                if($hasRoute && $this->Context->route()->hasGeohashes()) {
                    $Position = $Trackpoint->addChild('Position');
                    $Position->addChild('LatitudeDegrees', $Route->latitude());
                    $Position->addChild('LongitudeDegrees', $Route->longitude());

                
                if ($hasElevation) 
                    $Trackpoint->addChild('AltitudeMeters', $Route->current(Route\Entity::ELEVATIONS_ORIGINAL));
                
                }
                if($this->Context->trackdata()->has(Trackdata\Entity::DISTANCE))
                    $Trackpoint->addChild('DistanceMeters', 1000*$Trackdata->distance());

                if ($hasHeartrate) {
                    $Heartrate = $Trackpoint->addChild('HeartRateBpm');
                    $Heartrate->addChild('Value', $Trackdata->current(Trackdata\Entity::HEARTRATE));
                }
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
            '<TrainingCenterDatabase xmlns="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd">
	<Activities>
		<Activity>
		</Activity>
	</Activities>
	<Author xsi:type="Application_t">
		<Name>Runalyze</Name>
	</Author>
</TrainingCenterDatabase>';
    }
}
