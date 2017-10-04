<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use SimpleXMLElement;

class LogbookActivity extends AbstractSingleParser
{
    /** @var SimpleXMLElement */
    protected $Xml;

    public function __construct(SimpleXMLElement $activity)
    {
        parent::__construct();

        $this->checkThatActivityXmlIsValid($activity);

        $this->Xml = $activity;
    }

    protected function checkThatActivityXmlIsValid(SimpleXMLElement $activity)
    {
        if (empty($activity->attributes()->startTime)) {
            throw new UnsupportedFileException('Given XML object is not from SportTracks. &lt;Activity&gt;-tag has no attribute \'startTime\'.');
        }
    }

    public function parse()
    {
        $this->parseAttributesForMetadata();
        $this->parseAttributesForActivityData();
        $this->parseAdditionalAttributes();
        $this->parseLaps();
    }

    protected function parseAttributesForMetadata()
    {
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Xml['startTime']);

        if (!empty($this->Xml['categoryName'])) {
            $this->Container->Metadata->setSportName((string)$this->Xml['categoryName']);
        }

        if (!empty($this->Xml['name'])) {
            $this->Container->Metadata->setDescription((string)$this->Xml['name']);
        }

        if (!empty($this->Xml['notes'])) {
            $this->Container->Metadata->setNotes((string)$this->Xml['notes']);
        }

        if (!empty($this->Xml['location'])) {
            $this->Container->Metadata->setRouteDescription((string)$this->Xml['location']);
        }
    }

    protected function parseAttributesForActivityData()
    {
        if (!empty($this->Xml['totalTime'])) {
            $this->Container->ActivityData->Duration = (int)$this->Xml['totalTime'];
        }

        if (!empty($this->Xml['totalDistance'])) {
            $this->Container->ActivityData->Distance = ((int)$this->Xml['totalDistance']) / 1000;
        }

        if (!empty($this->Xml['averageHeartRate'])) {
            $this->Container->ActivityData->AvgHeartRate = (int)$this->Xml['averageHeartRate'];
        }

        if (!empty($this->Xml['maximumHeartRate'])) {
            $this->Container->ActivityData->MaxHeartRate = (int)$this->Xml['maximumHeartRate'];
        }

        if (!empty($this->Xml['totalCalories'])) {
            $this->Container->ActivityData->EnergyConsumption = (int)$this->Xml['totalCalories'];
        }

        if (!empty($this->Xml['totalAscend'])) {
            $this->Container->ActivityData->ElevationAscent = (int)$this->Xml['totalAscend'];
        }

        if (!empty($this->Xml['totalDescend'])) {
            $this->Container->ActivityData->ElevationDescent = -(int)$this->Xml['totalDescend'];
        }
    }

    protected function parseAdditionalAttributes()
    {
        if (property_exists($this->Xml, 'Weather')) {
            if (!empty($this->Xml->Weather['conditions'])) {
                $this->Container->WeatherData->Condition = (string)$this->Xml->Weather['conditions'];
            }

            if (!empty($this->Xml->Weather['temperatureCelsius'])) {
                $this->Container->WeatherData->Temperature = (float)$this->Xml->Weather['temperatureCelsius'];
            }
        }
    }

    protected function parseLaps()
    {
        $lapTimes = [];
        $lapDistances = [];
        $totalDistance = 0;
        $heartRateMultipliedByTime = 0;

        if (isset($this->Xml->Laps) && !empty($this->Xml->Laps)) {
            foreach ($this->Xml->Laps->Lap as $Lap) {
                $lapTimes[] = (int)$Lap['totalTime'];

                if (isset($Lap['avgHeartRate']) && (int)$Lap['avgHeartRate'] > 0) {
                    $heartRateMultipliedByTime += (int)$Lap['totalTime'] * (int)$Lap['avgHeartRate'];
                }
            }
        }

        if (isset($this->Xml->DistanceMarkers) && !empty($this->Xml->DistanceMarkers)) {
            foreach ($this->Xml->DistanceMarkers->DistanceMarker as $Marker) {
                $lapDistances[] = (int)$Marker['distance'] / 1000 - $totalDistance;
                $totalDistance += end($lapDistances);
            }

            $lapDistances[] = $this->Container->ActivityData->Distance - $totalDistance;
        }

        $numTimes = count($lapTimes);

        if ($numTimes > 0 && count($lapDistances) == $numTimes) {
            for ($i = 0; $i < $numTimes; ++$i) {
                $this->Container->Rounds->add(new Round(
                    $lapDistances[$i],
                    $lapTimes[$i]
                ));
            }
        }

        if ($heartRateMultipliedByTime > 0 && null === $this->Container->ActivityData->AvgHeartRate && $this->Container->ActivityData->Duration > 0) {
            $this->Container->ActivityData->AvgHeartRate = $heartRateMultipliedByTime / $this->Container->ActivityData->Duration;
        }
    }
}
