<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Activity\Duration;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileContentAwareParserTrait;

class Hrm extends AbstractSingleParser implements FileContentAwareParserInterface
{
    use FileContentAwareParserTrait;

    /** @var int data of 'Interval' for rr-data */
    const RR_DATA_INTERVAL = 238;

    /** @var string|bool */
    protected $CurrentLine = false;

    /** @var string */
    protected $CurrentHeader = '';

    /** @var int [s] */
    protected $TotalSplitsTime = 0;

    /** @var bool */
    protected $RecordsAltitude = true;

    /** @var bool uses imperial units (miles, mp/h, ft) */
    protected $UsesImperialUnits = false;

    /** @var float factor to transform km/h or mph to s/km */
    protected $PaceFactor = 3600.0;

    /** @var float factor to transform m or ft to m */
    protected $DistanceFactor = 1.0;

    /** @var int [s] */
    protected $RecordingInterval = 1;

    /** @var int [s] */
    protected $TotalTime = 0;

    public function parse()
    {
        $this->CurrentLine = strtok($this->FileContent, "\r\n");

        while ($this->CurrentLine !== false) {
            if ($this->CurrentLine[0] == '[') {
                $this->CurrentHeader = substr($this->CurrentLine, 1, -1);
            } else {
                $this->parseLine();
            }

            $this->CurrentLine = strtok("\r\n");
        }
    }

    protected function parseLine()
    {
        switch ($this->CurrentHeader) {
            case 'Params':
                $this->readParam();
                break;
            case 'IntTimes':
                $this->readLap();
                break;
            case 'HRData':
                $this->readHeartRateData();
                break;
        }
    }

    protected function readParam()
    {
        if (substr($this->CurrentLine, 0, 4) == 'Date') {
            $this->Container->Metadata->setTimestampAndTimezoneOffsetFrom(substr($this->CurrentLine, 5).' 00:00');
        } elseif (substr($this->CurrentLine, 0, 9) == 'StartTime') {
            $this->Container->Metadata->setTimestamp(
                $this->Container->Metadata->getTimestamp() + (new Duration(substr($this->CurrentLine, 10)))->seconds(),
                $this->Container->Metadata->getTimezoneOffset()
            );
        } elseif (substr($this->CurrentLine, 0, 6) == 'Length') {
            $this->Container->ActivityData->Duration = (new Duration(substr($this->CurrentLine, 7)))->seconds();
        } elseif (substr($this->CurrentLine, 0, 8) == 'Interval') {
            $this->RecordingInterval = (int)trim(substr($this->CurrentLine, 9));
        } elseif (substr($this->CurrentLine, 0, 4) == 'Mode') {
            $this->RecordsAltitude = (substr($this->CurrentLine, 5, 1) == '1');
            $this->UsesImperialUnits = (substr($this->CurrentLine, 7, 1) == '1');

            if ($this->UsesImperialUnits) {
                $this->setImperialFactors();
            }
        } elseif (substr($this->CurrentLine, 0, 5) == 'SMode') {
            $this->RecordsAltitude = (substr($this->CurrentLine, 8, 1) == '1');
            $this->UsesImperialUnits = (substr($this->CurrentLine, 13, 1) == '1');

            if ($this->UsesImperialUnits) {
                $this->setImperialFactors();
            }
        }
    }

    protected function setImperialFactors()
    {
        $this->PaceFactor = 3600.0 / 1.609344;
        $this->DistanceFactor = 0.305;
    }

    protected function readLap()
    {
        if (strpos($this->CurrentLine, ':')) {
            $time = new Duration(substr($this->CurrentLine, 0, 10));

            $this->Container->Rounds->add(new Round(0.0, $time->seconds() - $this->TotalSplitsTime));
            $this->TotalSplitsTime = $time->seconds();
        }
    }

    protected function readHeartRateData()
    {
        $values = preg_split('/[\s]+/', $this->CurrentLine);

        if ($this->RecordingInterval == self::RR_DATA_INTERVAL) {
            $rr = (int)trim($values[0]);

            if ($rr <= 0) {
                return;
            }

            $this->TotalTime += $rr / 1000;

            $this->Container->ContinuousData->Time[] = round($this->TotalTime);
            $this->Container->ContinuousData->HeartRate[] = round(60000 / $rr);
            $this->Container->RRIntervals[] = $rr;
        } else {
            $this->TotalTime += $this->RecordingInterval;

            $this->Container->ContinuousData->Time[] = $this->TotalTime;
            $this->Container->ContinuousData->HeartRate[] = (int)trim($values[0]);

            $pace = isset($values[1]) && (int)trim($values[1]) > 0 ? round($this->PaceFactor / ((int)trim($values[1]) / 10)) : 0.0;
            $dist = $pace > 0 ? $this->RecordingInterval / $pace : 0.0;

            $this->Container->ContinuousData->Distance[] = empty($this->Container->ContinuousData->Distance) ? $dist : $dist + end($this->Container->ContinuousData->Distance);

            if (count($values) > 3) {
                $this->Container->ContinuousData->Cadence[] = isset($values[2]) ? (int)trim($values[2]) : null;
                $this->Container->ContinuousData->Altitude[] = isset($values[3]) ? round((int)trim($values[3]) * $this->DistanceFactor) : null;
                $this->Container->ContinuousData->Power[] = isset($values[4]) ? (int)trim($values[4]) : null;
            } elseif ($this->RecordsAltitude) {
                $this->Container->ContinuousData->Altitude[] = isset($values[2]) ? round((int)trim($values[2]) * $this->DistanceFactor) : null;
            } else {
                $this->Container->ContinuousData->Cadence[] = isset($values[2]) ? (int)trim($values[2]) : null;
            }
        }
    }
}
