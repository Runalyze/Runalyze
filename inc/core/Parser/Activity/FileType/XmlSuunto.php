<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Activity\Common\StrtotimeWithLocalTimezoneOffsetTrait;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;
use SimpleXMLElement;

class XmlSuunto extends AbstractSingleParser implements FileContentAwareParserInterface
{
    use StrtotimeWithLocalTimezoneOffsetTrait;

    /** @var SimpleXMLElement */
    protected $Header;

    /** @var float|null */
    protected $CurrentLatitude = null;

    /** @var float|null */
    protected $CurrentLongitude = null;

    /** @var int [s] */
    protected $CurrentTime = 0;

    /** @var int [m] */
    protected $CurrentDistance = 0;

    /** @var bool */
    protected $UseRRIntervalsForHeartRate = false;

    /** @var int */
    protected $LastRRIndex = 0;

    /** @var int */
    protected $CurrentRRIndex = -1;

    /** @var float [s] */
    protected $RRSum = 0.0;

    /** @var float [s] */
    protected $RRSumForNextSample = 0.0;

    /** @var int */
    protected $NumRRIntervals = 0;

    use XmlParserTrait {
        setFileContent as protected setFileContentInTrait;
    }

    /**
     * @param string $content
     */
    public function setFileContent($content)
    {
        $this->setFileContentInTrait($this->addRootElement($content));
    }

    /**
     * @param string $fileContent
     * @return string
     */
    protected function addRootElement($fileContent)
    {
        $fileContent = substr($fileContent, strpos($fileContent, ">")+1);

        return "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<root>".$fileContent."</root>";
    }

    public function parse()
    {
        $this->checkThatXmlIsValid();
        $this->setHeader();

        $this->readRRintervals();
        $this->parseGeneralValues();
        $this->parseOptionalValues();
        $this->parseSamples();
    }

    protected function checkThatXmlIsValid()
    {
        if (
            (!property_exists($this->Xml, 'header') && !property_exists($this->Xml, 'Header')) ||
            (!property_exists($this->Xml, 'Samples') && !property_exists($this->Xml, 'samples'))
        ) {
            throw new UnsupportedFileException('Given XML object is not from Suunto. &lt;header&gt;- or &lt;Samples&gt;-tag could not be located.');
        }
    }

    protected function setHeader()
    {
        $this->Header = $this->Xml->header;
    }

    protected function readRRIntervals()
    {
        $this->Container->RRIntervals = $this->getRRIntervals();

        if (!empty($this->Container->RRIntervals)) {
            $this->UseRRIntervalsForHeartRate = true;
            $this->NumRRIntervals = count($this->Container->RRIntervals);
        }
    }

    /**
     * @return array
     */
    protected function getRRIntervals()
    {
        if (property_exists($this->Xml, 'R-R') && !empty($this->Xml->{'R-R'}->Data)) {
            return explode(' ', (string)$this->Xml->{'R-R'}->Data);
        }

        return [];
    }

    protected function parseGeneralValues()
    {
        $this->Container->Metadata->setCreator($this->getDeviceName(), 'Suunto');
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Header->DateTime);

        if (!empty($this->Header->Activity)) {
            $this->Container->Metadata->setSportName((string)$this->Header->Activity);
        }
    }

    /**
     * @return string
     */
    protected function getDeviceName()
    {
        if (property_exists($this->Xml, 'Device')) {
            return (string)$this->Xml->Device->Name;
        }

        return '';
    }

    protected function parseOptionalValues()
    {
        if (!empty($this->Header->Duration)) {
            $this->Container->ActivityData->Duration = (int)$this->Header->Duration;
        }

        if (!empty($this->Header->Distance)) {
            $this->Container->ActivityData->Distance = (int)$this->Header->Distance / 1000;
        }

        if (!empty($this->Header->Energy)) {
            $this->Container->ActivityData->EnergyConsumption = round((int)$this->Header->Energy / 4184);
        }

        if (!empty($this->Header->PoolLength)) {
            $this->Container->ActivityData->PoolLength = (int)$this->Header->PoolLength;
        }

        if (!empty($this->Header->PeakTrainingEffect)) {
            $this->Container->FitDetails->TrainingEffect = (float)$this->Header->PeakTrainingEffect;
        }
    }

    protected function parseSamples()
    {
        if (!empty($this->Xml->Samples)) {
            $this->parseSamplesFrom($this->Xml->Samples->Sample);
        } elseif (!empty($this->Xml->samples)) {
            $this->parseSamplesFrom($this->Xml->samples->sample);
        }

        if (min($this->Container->ContinuousData->Altitude) > 0) {
            $this->Container->ContinuousData->IsAltitudeDataBarometric = true;
        }
    }

    protected function parseSamplesFrom(SimpleXMLElement $samples)
    {
        foreach ($samples as $sample) {
            $this->parseSample($sample);
        }

        $this->readElapsedTimeFrom($samples[$samples->count() - 1]);
    }

    protected function readElapsedTimeFrom(SimpleXMLElement $sample)
    {
        if (!empty($sample->UTC)) {
            $lastTimestamp = (int)$this->strtotime((string)$sample->UTC);

            if ($lastTimestamp > $this->Container->Metadata->getTimestamp())
                $this->Container->ActivityData->ElapsedTime = $lastTimestamp - $this->Container->Metadata->getTimestamp();
        }
    }

    protected function parseSample(SimpleXMLElement $sample)
    {
        $this->addRoundIfAvailableFrom($sample);
        $this->readCurrentLocationFrom($sample);


        if ((string)$sample->SampleType == 'periodic') {
            $this->parsePeriodicSample($sample);
        }
    }

    protected function addRoundIfAvailableFrom(SimpleXMLElement $sample)
    {
        if (!empty($sample->Events)) {
            if (!empty($sample->Events->Lap) && !empty($sample->Events->Lap->Distance) && !empty($sample->Events->Lap->Duration)) {
                $this->Container->Rounds->add(
                    new Round(
                        round((int)$sample->Events->Lap->Distance) / 1000,
                        (int)$sample->Events->Lap->Duration
                    )
                );
            }
        }
    }

    protected function readCurrentLocationFrom(SimpleXMLElement $sample)
    {
        if (!empty($sample->Latitude) && !empty($sample->Longitude)) {
            $this->CurrentLatitude = round((float)$sample->Latitude * 180 / pi(), 7);
            $this->CurrentLongitude = round((float)$sample->Longitude * 180 / pi(), 7);
        }
    }

    protected function parsePeriodicSample(SimpleXMLElement $sample)
    {
        if (
            (!empty($sample->Distance) && (int)$sample->Distance <= $this->CurrentDistance)
            || (!empty($sample->Time) && (int)$sample->Time <= $this->CurrentTime)
        ) {
            return;
        }

        $this->CurrentDistance = (int)$sample->Distance;
        $this->CurrentTime = (int)$sample->Time;

        while ($this->RRSum < $this->CurrentTime && $this->CurrentRRIndex < $this->NumRRIntervals - 1) {
            $this->CurrentRRIndex++;
            $this->RRSumForNextSample += $this->Container->RRIntervals[$this->CurrentRRIndex] / 1000;
            $this->RRSum += $this->Container->RRIntervals[$this->CurrentRRIndex] / 1000;
        }

        $this->setContinuousDataFrom($sample);
    }

    protected function setContinuousDataFrom(SimpleXMLElement $sample)
    {
        if (!empty($sample->HR)) {
            $hr = round(60 * (float)$sample->HR);
            $this->UseRRIntervalsForHeartRate = false;
        } elseif ($this->UseRRIntervalsForHeartRate) {
            if ($this->CurrentRRIndex >= $this->LastRRIndex) {
                $hr = round(60 / $this->RRSumForNextSample * ($this->CurrentRRIndex - $this->LastRRIndex + 1));

                $this->LastRRIndex = $this->CurrentRRIndex;
                $this->RRSumForNextSample = $this->Container->RRIntervals[$this->CurrentRRIndex] / 1000;
            } else {
                $hr = null;
            }
        } else {
            $hr = $this->getCurrentValueOf($this->Container->ContinuousData->HeartRate);
        }

        $this->Container->ContinuousData->Time[] = $this->CurrentTime;
        $this->Container->ContinuousData->Distance[] = (float)$sample->Distance / 1000;
        $this->Container->ContinuousData->Latitude[] = $this->CurrentLatitude;
        $this->Container->ContinuousData->Longitude[] = $this->CurrentLongitude;
        $this->Container->ContinuousData->HeartRate[] = $hr;
        $this->Container->ContinuousData->Altitude[] = !empty($sample->Altitude) ? (int)$sample->Altitude : $this->getCurrentValueOf($this->Container->ContinuousData->Altitude);
        $this->Container->ContinuousData->Temperature[] = !empty($sample->Temperature) ? round((float)$sample->Temperature - 273.15) : $this->getCurrentValueOf($this->Container->ContinuousData->Temperature);
        $this->Container->ContinuousData->Cadence[] = !empty($sample->Cadence) ? (float)$sample->Cadence * 60 : $this->getCurrentValueOf($this->Container->ContinuousData->Cadence);
    }

    /**
     * @param array $data
     * @return mixed|null
     */
    protected function getCurrentValueOf(array $data)
    {
        if (!empty($data)) {
            return end($data);
        }

        return null;
    }
}
