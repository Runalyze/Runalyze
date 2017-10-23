<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Activity\Duration;
use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use SimpleXMLElement;

class XmlPolarExercise extends AbstractSingleParser
{
    /** @var SimpleXMLElement */
    protected $Xml;

    public function __construct(SimpleXMLElement $exercise)
    {
        parent::__construct();

        $this->checkThatActivityXmlIsValid($exercise);

        $this->Xml = $exercise;
    }

    protected function checkThatActivityXmlIsValid(SimpleXMLElement $exercise)
    {
        if (empty($exercise->result)) {
            throw new UnsupportedFileException('Given XML object is not from Polar. &lt;result&gt;-tag could not be located.');
        }
    }

    public function parse()
    {
        $this->parseGeneralValues();
        $this->parseOptionalValues();
        $this->parseLaps();
        $this->parseSamples();
    }

    protected function parseGeneralValues()
    {
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Xml->time);
    }

    protected function parseOptionalValues()
    {
        if (isset($this->Xml->result->distance)) {
            $this->Container->ActivityData->Distance = (double)$this->Xml->result->distance / 1000;
        }

        if (isset($this->Xml->result->duration)) {
            $this->Container->ActivityData->Duration = $this->stringToDuration((string)$this->Xml->result->duration);
        }

        if (isset($this->Xml->result->calories)) {
            $this->Container->ActivityData->EnergyConsumption = (int)$this->Xml->result->calories;
        }

        if (isset($this->Xml->result->{'heart-rate'})) {
            $this->Container->ActivityData->AvgHeartRate = (int)$this->Xml->result->{'heart-rate'}->average;
            $this->Container->ActivityData->MaxHeartRate = (int)$this->Xml->result->{'heart-rate'}->maximum;
        }

        if (isset($this->Xml->note)) {
            $this->Container->Metadata->setNotes((string)$this->Xml->note);
        }

        if (isset($this->Xml->name)) {
            $this->Container->Metadata->setDescription((string)$this->Xml->name);
        }
    }

    protected function parseLaps()
    {
        if (isset($this->Xml->result->laps)) {
            foreach ($this->Xml->result->laps->lap as $lap) {
                $distance = round(((double)$lap->distance) / 1000, 3);
                $seconds = $this->stringToDuration((string)$lap->duration);

                if (($seconds / (double)$lap->distance) < 0.06) {
                    $seconds = $seconds * 60;
                }

                $this->Container->Rounds->add(new Round($distance, $seconds));
            }
        }
    }

    protected function parseSamples()
    {
        $num = 0;
        $interval = (int)$this->Xml->result->{'recording-rate'};

        if (isset($this->Xml->result->samples)) {
            foreach ($this->Xml->result->samples->sample as $sample) {
                $data = explode(',', (string)$sample->values);

                if (end($data) == '') {
                    array_pop($data);
                }

                $num = count($data);

                switch ((string)$sample->type) {
                    case 'HEARTRATE':
                        $this->Container->ContinuousData->HeartRate = $data;
                        break;

                    case 'ALTITUDE':
                        $this->Container->ContinuousData->Altitude = $data;
                        break;

                    case 'DISTANCE':
                        $this->Container->ContinuousData->Distance = array_map(function($m) {
                            return $m / 1000;
                        }, $data);
                        break;

                    case 'RUN_CADENCE':
                        $this->Container->ContinuousData->Cadence = $data;
                        break;
                }
            }
        }

        if ($interval > 0 && $num > 0) {
            $this->Container->ContinuousData->Time = range(0, ($num - 1) * $interval, $interval);
        }
    }

    /**
     * @param string $string "[[[z\d ]H:]i:]s[(.|,)u]"
     * @return float [s]
     */
    protected function stringToDuration($string)
    {
        return (new Duration($string))->seconds();
    }
}
