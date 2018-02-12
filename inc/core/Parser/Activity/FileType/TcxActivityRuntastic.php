<?php

namespace Runalyze\Parser\Activity\FileType;

use SimpleXMLElement;

class TcxActivityRuntastic extends TcxActivity
{
    /** @var int */
    protected $CurrentIndex = 0;

    /** @var int */
    protected $LastActiveIndex = 0;

    /** @var int [bpm] */
    protected $LastValidHR = 0;

    protected function parseGeneralValues()
    {
        parent::parseGeneralValues();

        $this->Container->ActivityData->Duration = (string)$this->Xml->Lap->TotalTimeSeconds;
    }

    protected function parseTrackpoint(SimpleXMLElement &$trackPoint)
    {
        if ($this->DistancesAreEmpty) {
            $trackPoint->addChild('DistanceMeters', 1000 * $this->distanceToTrackpoint($trackPoint));
        }

        if (!empty($trackPoint->HeartRateBpm)) {
            $this->LastValidHR = round($trackPoint->HeartRateBpm->Value);
        }

        $this->Container->ContinuousData->Time[] = $this->strtotime((string)$trackPoint->Time) - $this->Container->Metadata->getTimestamp();
        $this->Container->ContinuousData->Distance[] = (float)$trackPoint->DistanceMeters / 1000;
        $this->Container->ContinuousData->Altitude[] = (int)$trackPoint->AltitudeMeters;
        $this->Container->ContinuousData->HeartRate[] = $this->LastValidHR;

        if (!empty($trackPoint->Position)) {
            $this->Container->ContinuousData->Latitude[] = (double)$trackPoint->Position->LatitudeDegrees;
            $this->Container->ContinuousData->Longitude[] = (double)$trackPoint->Position->LongitudeDegrees;
        } elseif (!empty($this->Container->ContinuousData->Latitude)) {
            $this->Container->ContinuousData->Latitude[] = end($this->Container->ContinuousData->Latitude);
            $this->Container->ContinuousData->Longitude[] = end($this->Container->ContinuousData->Longitude);
        } else {
            $this->Container->ContinuousData->Latitude[] = 0;
            $this->Container->ContinuousData->Longitude[] = 0;
        }

        $this->CurrentIndex++;

        $this->parseExtensionValues($trackPoint);
    }

    /**
     * @return int
     */
    protected function getCurrentPaceForRuntastic()
    {
        $currDist = $this->Container->ContinuousData->Distance[$this->CurrentIndex];
        $lastDist = $this->Container->ContinuousData->Distance[$this->LastActiveIndex];
        $currTime = $this->Container->ContinuousData->Time[$this->CurrentIndex];
        $lastTime = $this->Container->ContinuousData->Time[$this->LastActiveIndex];

        if ($currDist > $lastDist) {
            return round( ($currTime - $lastTime) / ($currDist - $lastDist) );
        }

        return 0;
    }
}
