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
    /** @var \SimpleXMLElement */
    protected $XML = null;

    /** @var \SimpleXMLElement */
    protected $Activity = null;

    /** @var bool */
    protected $HasRoute = false;

    /** @var array indicators if route has specific data, use const from Route\Entity as array key */
    protected $RouteHas = [];

    /** @var array indicators if route has specific data, use const from Trackdata\Entity as array key */
    protected $TrackdataHas = [];

    /**
     * @return bool
     */
    public function isPossible()
    {
        return $this->Context->hasTrackdata() && $this->Context->trackdata()->has(Trackdata\Entity::TIME);
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

        $this->setInternalIndicators();
        $this->setGeneralInfo();
        $this->setLaps();

        $this->FileContent = $this->XML->asXML();

        $this->formatFileContentAsXML();
    }

    /**
     * Fill internal arrays $TrackdataHas and $RouteHas
     */
    protected function setInternalIndicators()
    {
        $this->setInternalIndicatorsForRoute();
        $this->setInternalIndicatorsForTrackdata();
    }

    /**
     * Fill internal array $RouteHas
     */
    protected function setInternalIndicatorsForRoute()
    {
        if ($this->Context->hasRoute()) {
            $this->HasRoute = true;
            $this->RouteHas[Route\Entity::GEOHASHES] = $this->Context->route()->hasGeohashes();
            $this->RouteHas[Route\Entity::ELEVATIONS_ORIGINAL] = $this->Context->route()->hasOriginalElevations();
        } else {
            $this->HasRoute = false;
        }
    }

    /**
     * Fill internal array $TrackdataHas
     */
    protected function setInternalIndicatorsForTrackdata()
    {
        $this->TrackdataHas[Trackdata\Entity::DISTANCE] = $this->Context->trackdata()->has(Trackdata\Entity::DISTANCE);
        $this->TrackdataHas[Trackdata\Entity::HEARTRATE] = $this->Context->trackdata()->has(Trackdata\Entity::HEARTRATE);
        $this->TrackdataHas[Trackdata\Entity::CADENCE] = $this->Context->trackdata()->has(Trackdata\Entity::CADENCE);
        $this->TrackdataHas[Trackdata\Entity::POWER] = $this->Context->trackdata()->has(Trackdata\Entity::POWER);
        $this->TrackdataHas[Trackdata\Entity::PACE] = $this->Context->trackdata()->has(Trackdata\Entity::PACE);
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
            if ($this->TrackdataHas[Trackdata\Entity::DISTANCE]) {
                $Loop->nextKilometer();
            } else {
                $Loop->goToEnd();
            }

            $TimeInSeconds = $Loop->difference(Trackdata\Entity::TIME);

            $Lap = $this->Activity->addChild('Lap');
            $Lap->addAttribute('StartTime', $this->timeToString($Starttime + $Loop->time() - $TimeInSeconds));
            $Lap->addChild('TotalTimeSeconds', $TimeInSeconds);
            $Lap->addChild('DistanceMeters', 1000*$Loop->difference(Trackdata\Entity::DISTANCE));

            if ($this->TrackdataHas[Trackdata\Entity::HEARTRATE]) {
                $Lap->addChild('AverageHeartRateBpm')->addChild('Value', $Loop->average(Trackdata\Entity::HEARTRATE));
                $Lap->addChild('MaximumHeartRateBpm')->addChild('Value', $Loop->max(Trackdata\Entity::HEARTRATE));
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

        if ($this->HasRoute) {
            $Route = new Route\Loop($this->Context->route());
        }

        while ($Trackdata->nextStep()) {
            if ($this->HasRoute) {
                $Route->nextStep();
            }

            if ($this->Activity->Lap[(int)floor($Trackdata->distance())]) {
                $Trackpoint = $this->Activity->Lap[(int)floor($Trackdata->distance())]->Track->addChild('Trackpoint');
                $Trackpoint->addChild('Time', $this->timeToString($Starttime + $Trackdata->time()));

                if ($this->HasRoute) {
                    $this->addRouteDetailsTo($Trackpoint, $Route);
                }

                $this->addTrackdataDetailsTo($Trackpoint, $Trackdata);
            }
        }
    }

    /**
     * @param \SimpleXMLElement $trackpoint
     * @param \Runalyze\Model\Route\Loop $routeLoop
     */
    protected function addRouteDetailsTo(\SimpleXMLElement $trackpoint, Route\Loop $routeLoop)
    {
        if ($this->RouteHas[Route\Entity::GEOHASHES]) {
            $Position = $trackpoint->addChild('Position');
            $Position->addChild('LatitudeDegrees', $routeLoop->latitude());
            $Position->addChild('LongitudeDegrees', $routeLoop->longitude());
        }

        if ($this->RouteHas[Route\Entity::ELEVATIONS_ORIGINAL]) {
            $trackpoint->addChild('AltitudeMeters', $routeLoop->current(Route\Entity::ELEVATIONS_ORIGINAL));
        }
    }

    /**
     * @param \SimpleXMLElement $trackpoint
     * @param \Runalyze\Model\Trackdata\Loop $trackdataLoop
     */
    protected function addTrackdataDetailsTo(\SimpleXMLElement $trackpoint, Trackdata\Loop $trackdataLoop)
    {
        if ($this->TrackdataHas[Trackdata\Entity::CADENCE]) {
            $trackpoint->addChild('Cadence', $trackdataLoop->current (Trackdata\Entity::CADENCE));
        }

        if ($this->TrackdataHas[Trackdata\Entity::DISTANCE]) {
            $trackpoint->addChild('DistanceMeters', 1000*$trackdataLoop->distance());
        }

        if ($this->TrackdataHas[Trackdata\Entity::HEARTRATE]) {
            $trackpoint->addChild('HeartRateBpm')->addChild('Value', $trackdataLoop->current(Trackdata\Entity::HEARTRATE));
        }

        if ($this->TrackdataHas[Trackdata\Entity::POWER] || $this->TrackdataHas[Trackdata\Entity::PACE]) {
            $TPX = $trackpoint->addChild('Extensions')->addChild('TPX', '', 'http://www.garmin.com/xmlschemas/ActivityExtension/v2');

            if ($this->TrackdataHas[Trackdata\Entity::PACE]) {
                $TPX->addChild('Speed', $this->convertPaceToSpeed($trackdataLoop->current(Trackdata\Entity::PACE)));
            }

            if ($this->TrackdataHas[Trackdata\Entity::POWER]) {
                $TPX->addChild('Watts', $trackdataLoop->current(Trackdata\Entity::POWER));
            }
        }
    }

    /**
     * @param int $pace
     * @return float
     */
    protected function convertPaceToSpeed($pace)
    {
        if ($pace == 0) {
            return 0.0;
        }

        return 1000 / $pace;
    }

    /**
     * Get empty xml
     * @return string
     */
    protected function emptyXml()
    {
        return
            '<TrainingCenterDatabase xmlns="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd" xmlns:ns3="http://www.garmin.com/xmlschemas/ActivityExtension/v2">
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
