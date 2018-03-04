<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;

class KmlExtended extends AbstractSingleParser implements FileContentAwareParserInterface
{
    use XmlParserTrait;

    /** @var int [s] */
    protected $PauseInSeconds = 0;

    /** @var int */
    protected $PauseIndex = 0;

    /** @var array */
    protected $PauseTimes = [];

    /** @var array */
    protected $ResumeTimes = [];

    public function parse()
    {
        $this->preparePauses();
        $this->parseGeneralValues();
        $this->parseCoordinates();
        $this->parseTimeSteps();
        $this->parseExtendedData();
        $this->interpretTimestampAsServerTime();
    }

    protected function parseGeneralValues()
    {
        $when = $this->Xml->xpath('//when');

        $this->Container->Metadata->setTimestamp(strtotime((string)$when[0]));
    }

    protected function interpretTimestampAsServerTime()
    {
        $this->Container->Metadata->interpretTimestampAsServerTime($this->Container->Metadata->getTimestamp());
    }

    protected function parseCoordinates()
    {
        foreach ($this->Xml->xpath('//gx:coord') as $coord) {
            $parts = explode(' ', (string)$coord);
            $num = count($parts);

            if (($num == 3 || $num == 2) && ($parts[0] != 0.0 || $parts[1] != 0.0)) {
                $this->Container->ContinuousData->Latitude[] = $parts[1];
                $this->Container->ContinuousData->Longitude[] = $parts[0];
                $this->Container->ContinuousData->Altitude[] = ($num > 2) ? $parts[2] : null;
            }
        }
    }

    protected function parseTimeSteps()
    {
        $startTime = $this->Container->Metadata->getTimestamp();
        $DstCorrector = date('I') - date('I', $startTime);

        foreach ($this->Xml->xpath('//when') as $step) {
            $time = strtotime((string)$step);

            if ($this->hasMorePauses()) {
                $currentTime = strftime('%T', $time + $DstCorrector * 3600);

                if ($currentTime == $this->ResumeTimes[$this->PauseIndex]) {
                    $this->Container->Pauses->add(new Pause(
                        end($this->Container->ContinuousData->Time),
                        $this->PauseTimes[$this->PauseIndex]
                    ));

                    $this->PauseInSeconds += $this->PauseTimes[$this->PauseIndex];
                    $this->PauseIndex++;
                }
            }

            $this->Container->ContinuousData->Time[] = $time - $startTime - $this->PauseInSeconds;
        }
    }

    protected function parseExtendedData()
    {
        foreach ($this->Xml->xpath('//gx:SimpleArrayData') as $array) {
            $values = $array->xpath('gx:value');

            switch (strtolower($array['name'])) {
                case 'calories':
                    $this->parseExtendedCalories($values);
                    break;
                case 'distance':
                    $this->parseExtendedDistance($values);
                    break;
                case 'heartrate':
                    $this->parseExtendedHeartRate($values);
                    break;
                case 'power':
                    $this->parseExtendedPower($values);
                    break;
                case 'cadence':
                    $this->parseExtendedCadence($values);
                    break;
                case 'temperature':
                    $this->parseExtendedTemperature($values);
                    break;
            }
        }
    }

    protected function parseExtendedCalories(array $values)
    {
        $kcal = array_pop($values);

        $this->Container->ActivityData->EnergyConsumption = (int)$kcal;
    }

    protected function parseExtendedDistance(array $values)
    {
        foreach ($values as $value) {
            $this->Container->ContinuousData->Distance[] = (float)$value / 1000;
        }
    }

    protected function parseExtendedHeartRate(array $values)
    {
        foreach ($values as $value) {
            $this->Container->ContinuousData->HeartRate[] = (int)$value;
        }
    }

    protected function parseExtendedPower(array $values)
    {
        foreach ($values as $value) {
            $this->Container->ContinuousData->Power[] = (int)$value;
        }
    }

    protected function parseExtendedCadence(array $values)
    {
        foreach ($values as $value) {
            $this->Container->ContinuousData->Cadence[] = (int)$value;
        }
    }

    protected function parseExtendedAltitude(array $values)
    {
        foreach ($values as $value) {
            $this->Container->ContinuousData->Altitude[] = (int)$value;
        }
    }

    protected function parseExtendedTemperature(array $values)
    {
        foreach ($values as $value) {
            $this->Container->ContinuousData->Temperature[] = (int)$value;
        }
    }

    protected function preparePauses()
    {
        $start = 0;

        foreach ($this->Xml->Document->Folder as $folder) {
            if ($folder['id'] == 'pause_resume') {
                foreach ($folder->Placemark as $mark) {
                    if ($mark->styleUrl == '#pause') {
                        $start = strtotime((string)$mark->description.'Z');
                    } elseif ($mark->styleUrl == '#resume') {
                        $end = strtotime((string)$mark->description.'Z');

                        $this->ResumeTimes[] = strftime('%T', $end);
                        $this->PauseTimes[] = ($end - $start) % DAY_IN_S;
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function hasMorePauses()
    {
        return $this->PauseIndex < count($this->PauseTimes);
    }
}
