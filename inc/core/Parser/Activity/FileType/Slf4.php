<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use SimpleXMLElement;

class Slf4 extends Slf3
{
    protected function checkThatXmlIsValid()
    {
        if (!property_exists($this->Xml, 'Entries')) {
            throw new UnsupportedFileException('Given XML object is not from Sigma. &lt;Entries&gt;-tag could not be located.');
        }
    }

    protected function parseGeneralValues()
    {
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Xml->GeneralInformation->startDate);
        $this->Container->Metadata->setCreator('Sigma', $this->findCreator());
        $this->Container->Metadata->setDescription((string)$this->Xml->GeneralInformation->name);

        $this->Container->ActivityData->EnergyConsumption = (int)$this->Xml->GeneralInformation->calories;
    }

    protected function parseLogEntries()
    {
        if (!isset($this->Xml->Entries->Entry) ) {
            if ($this->Xml->GeneralInformation->trainingTime) {
                $this->Container->ActivityData->Duration = (int)$this->Xml->GeneralInformation->trainingTime / 100;
                $this->Container->ActivityData->Distance = (float)$this->Xml->GeneralInformation->distance / 1000;
                $this->Container->ActivityData->MaxHeartRate = (int)$this->Xml->GeneralInformation->maximumHeartrate;
                $this->Container->ActivityData->AvgHeartRate = (int)$this->Xml->GeneralInformation->averageHeartrate;
            } else {
                throw new UnsupportedFileException('This file does not contain any data.');
            }
        } else {
            foreach ($this->Xml->Entries->Entry as $log) {
                $this->parseLogEntry($log);
            }
        }
    }

    protected function parseLogEntry(SimpleXMLElement $log)
    {
        $log = $log->attributes();

        if ((int)$log['trainingTime'] == 0) {
            return;
        }

        $this->Container->ContinuousData->Time[] = (int)$log['trainingTimeAbsolute'] / 100;
        $this->Container->ContinuousData->Distance[] = (float)$log['distanceAbsolute'] / 1000;
        $this->Container->ContinuousData->HeartRate[] = (!empty($log['heartrate'])) ? round((float)$log['heartrate']) : null;
    }

    protected function parseLaps()
    {
        if (!empty($this->Xml->Markers)) {
            $readEnergyConsumption = null === $this->Container->ActivityData->EnergyConsumption;

            foreach ($this->Xml->Markers->Marker as $lap) {
                $this->parseLap($lap, $readEnergyConsumption);
            }
        }
    }

    protected function parseLap(SimpleXMLElement $lap, $readEnergyConsumption = true)
    {
        $lap = $lap->attributes();

        if ($readEnergyConsumption && !empty($lap['calories'])) {
            $this->Container->ActivityData->EnergyConsumption += (int)$lap['calories'];
        }

        $this->Container->Rounds->add(new Round(
            round((int)$lap['distance']) / 1000,
            round((float)$lap['time']),
            ((string)$lap['FastLap'] != 'false')
        ));
    }
}
