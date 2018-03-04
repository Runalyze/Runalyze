<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use SimpleXMLElement;

class FitlogActivity extends AbstractSingleParser
{
    /** @var SimpleXMLElement */
    protected $Xml;

    /** @var string */
    protected $StartTimeString = '';

    public function __construct(SimpleXMLElement $activity)
    {
        parent::__construct();

        $this->checkThatActivityXmlIsValid($activity);

        $this->Xml = $activity;
    }

    protected function checkThatActivityXmlIsValid(SimpleXMLElement $activity)
    {
        if (!property_exists($activity, 'Duration')) {
            throw new UnsupportedFileException('Given XML object is not from SportTracks. &lt;Duration&gt;-tag could not be located.');
        }
    }

    public function parse()
    {
        $this->parseGeneralValues();
        $this->parseLaps();
        $this->parseTrack();
        $this->parsePauses();
    }

    protected function parseGeneralValues()
    {
        $this->StartTimeString = (string)$this->Xml['StartTime'];
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom($this->StartTimeString);

        if (!empty($this->Xml['categoryName'])) {
            $this->Container->Metadata->setSportName((string)$this->Xml['categoryName']);
        }

        if (!empty($this->Xml->Category['Name'])) {
            $this->Container->Metadata->setSportName((string)$this->Xml->Category['Name']);
        }

        if (!empty($this->Xml->Duration['TotalSeconds'])) {
            $this->Container->ActivityData->Duration = round((double)$this->Xml->Duration['TotalSeconds']);
        }

        if (!empty($this->Xml->Distance['TotalMeters'])) {
            $this->Container->ActivityData->Distance = round((double)$this->Xml->Distance['TotalMeters']) / 1000;
        }

        if (!empty($this->Xml->Calories['TotalCal'])) {
            $this->Container->ActivityData->EnergyConsumption = (int)$this->Xml->Calories['TotalCal'];
        }

        if (!empty($this->Xml->Location['Name'])) {
            $this->Container->Metadata->setRouteDescription((string)$this->Xml->Location['Name']);
        }

        if (!empty($this->Xml->Weather['Temp'])) {
            $this->Container->WeatherData->Temperature = (int)$this->Xml->Weather['Temp'];
        }

        if (!empty($this->Xml->HeartRateMaximumBPM)) {
            $this->Container->ActivityData->MaxHeartRate = (int)$this->Xml->HeartRateMaximumBPM;
        }

        if (!empty($this->Xml->HeartRateAverageBPM)) {
            $this->Container->ActivityData->AvgHeartRate = (int)$this->Xml->HeartRateAverageBPM;
        }
    }

    protected function parseTrack()
    {
        if (isset($this->Xml->Track->pt)) {
            foreach ($this->Xml->Track->pt as $point) {
                $this->parseTrackPoint($point);
            }
        }
    }

    protected function parseTrackPoint(SimpleXMLElement $point)
    {
        if (!empty($point['lat'])) {
            $this->Container->ContinuousData->Latitude[] = round((double)$point['lat'], 7);
            $this->Container->ContinuousData->Longitude[] = round((double)$point['lon'], 7);
        } elseif (count($this->Container->ContinuousData->Latitude)) {
            $this->Container->ContinuousData->Latitude[] = end($this->Container->ContinuousData->Latitude);
            $this->Container->ContinuousData->Longitude[] = end($this->Container->ContinuousData->Longitude);
        }

        $this->Container->ContinuousData->Time[] = (int)$point['tm'];
        $this->Container->ContinuousData->Altitude[] = !empty($point['ele']) ? (int)$point['ele'] : 0;
        $this->Container->ContinuousData->HeartRate[] = !empty($point['hr']) ? (int)$point['hr'] : 0;
    }

    protected function parseLaps()
    {
        if (!isset($this->Xml->Laps)) {
            return;
        }

        $distance = 0;
        $kcal = 0;

        foreach ($this->Xml->Laps->children() as $lap) {
            $lapDistance = !empty($lap->Distance['TotalMeters']) ? ((int)$lap->Distance['TotalMeters']) / 1000 : 0.0;
            $distance += $lapDistance;

            if (!empty($lap['DurationSeconds'])) {
                $this->Container->Rounds->add(new Round($lapDistance, (int)$lap['DurationSeconds']));
            }

            if (!empty($lap->Distance['TotalCal'])) {
                $kcal += (int)$lap->Calories['TotalCal'];
            }
        }

        if ($distance > 0) {
            $this->Container->ActivityData->Distance = $distance;
        }

        if ($kcal > 0) {
            $this->Container->ActivityData->EnergyConsumption = $kcal;
        }
    }

    protected function parsePauses()
    {
        if (isset($this->Xml->TrackClock)) {
            foreach ($this->Xml->TrackClock->children() as $pause) {
                $this->Container->PausesToApply->add(new Pause(
                    strtotime((string)$pause['StartTime']) - strtotime($this->StartTimeString),
                    strtotime((string)$pause['EndTime']) - strtotime((string)$pause['StartTime'])
                ));
            }
        }
    }
}
