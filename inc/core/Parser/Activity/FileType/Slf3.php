<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;
use SimpleXMLElement;

class Slf3 extends AbstractSingleParser implements FileContentAwareParserInterface
{
    use XmlParserTrait;

    /** @var int [s] */
    protected $PauseInSeconds = 0;

    public function parse()
    {
        $this->checkThatXmlIsValid();

        $this->parseGeneralValues();
        $this->parseLogEntries();
        $this->parseLaps();
    }

    protected function checkThatXmlIsValid()
    {
        if (!property_exists($this->Xml, 'LogEntries')) {
            throw new UnsupportedFileException('Given XML object is not from Sigma. &lt;LogEntries&gt;-tag could not be located.');
        }
    }

    protected function parseGeneralValues()
    {
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Xml->GeneralInformation->StartDate);
        $this->Container->Metadata->setCreator('Sigma', $this->findCreator());
    }

    protected function parseLogEntries()
    {
        if (empty($this->Xml->LogEntries->LogEntry)) {
            throw new UnsupportedFileException('This file does not contain any data.');
        } else {
            foreach ($this->Xml->LogEntries->LogEntry as $log) {
                $this->parseLogEntry($log);
            }
        }
    }

    protected function parseLogEntry(SimpleXMLElement $log)
    {
        if ((int)$log->Time == 0 || (string)$log->IsPause == 'true') {
            if ((int)$log->PauseTime > 0 && !empty($this->Container->ContinuousData->Time)) {
                $this->Container->Pauses->add(new Pause(
                    end($this->Container->ContinuousData->Time),
                    (int)$log->PauseTime
                ));
            }

            return;
        }

        $this->Container->ContinuousData->Time[] = (int)$log->TimeAbsolute;
        $this->Container->ContinuousData->Distance[] = (float)$log->DistanceAbsolute / 1000;
        $this->Container->ContinuousData->HeartRate[] = (!empty($log->Heartrate)) ? round((float)$log->Heartrate) : null;
    }

    protected function parseLaps()
    {
        if (!empty($this->Xml->Laps)) {
            $readEnergyConsumption = null === $this->Container->ActivityData->EnergyConsumption;

            foreach ($this->Xml->Laps->Lap as $lap) {
                $this->parseLap($lap, $readEnergyConsumption);
            }
        }
    }

    protected function parseLap(SimpleXMLElement $lap, $readEnergyConsumption = true)
    {
        if ($readEnergyConsumption && !empty($lap->Calories)) {
            $this->Container->ActivityData->EnergyConsumption += (int)$lap->Calories;
        }

        $this->Container->Rounds->add(new Round(
            round((int)$lap->Distance) / 1000,
            round((float)$lap->Time),
            ((string)$lap->FastLap != 'false')
        ));
    }

    /**
     * @return string
     */
    protected function findCreator()
    {
        $string = '';

        if (!empty($this->Xml->GeneralInformation)) {
            foreach ($this->Xml->GeneralInformation->attributes() as $key => $value) {
                $string .= (string)$key.': '.((string)$value)."\n";
            }
        }

        return $string;
    }
}
