<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileContentAwareParserTrait;

class CsvEpson extends AbstractSingleParser implements FileContentAwareParserInterface
{
    use FileContentAwareParserTrait;

    /** @var string */
    protected $CurrentHeader = '';

    /** @var array */
    protected $TmpData = [];

    /** @var string */
    protected $GraphKey = '';

    /** @var string */
    protected $GraphString = '';

    public function parse()
    {
        $line = strtok($this->FileContent, "\n");

        while ($line !== false) {
            $line = trim($line);

            if (substr($line, 0, 1) == '[') {
                $this->CurrentHeader = substr($line, 1, -1);
                $this->TmpData = [];
            } else {
                $this->parseLine($line);
            }

            $line = strtok("\n");
        }

        $this->finishGpsData();
        $this->adjustArraySizes();
    }

    /**
     * @param string $line
     */
    protected function parseLine($line)
    {
        switch ($this->CurrentHeader) {
            case 'TrainingResult':
                $this->readTrainingResult($line);
                break;

            case 'TrainingData':
                $this->readTrainingData($line);
                break;

            case 'GraphData':
                $this->readGraphData($line);
                break;

            case 'GPSData':
                $this->readGPSData($line);
                break;

            case 'LapData':
                $this->readLapData($line);
                break;

            case 'TrainingSettingData':
                break;
        }
    }

    /**
     * @param string $line
     */
    protected function readTrainingResult($line) {
        if (substr($line, 0, 13) == 'TrainingName,') {
            $this->Container->Metadata->setDescription(substr($line, 13));
        } elseif (substr($line, 0, 5) == 'Memo,') {
            $this->Container->Metadata->setNotes(substr($line, 5));
        } elseif (substr($line, 0, 15) == 'TrainingKindId,') {
            $this->TmpData = explode(',', $line);
        } elseif (!empty($this->TmpData)) {
            $values = explode(',', $line);
            $startDay = '';
            $startTime = '';

            if (count($values) == count($this->TmpData)) {
                foreach ($this->TmpData as $i => $label) {
                    if (strlen($values[$i]) > 0) {
                        switch ($label) {
                            case 'StartDay':
                                $startDay = $values[$i];
                                break;

                            case 'StartTime':
                                $startTime = $values[$i];
                                break;

                            case 'TrainingTime':
                                $this->Container->ActivityData->Duration = $values[$i];
                                break;

                            case 'Distance':
                                $this->Container->ActivityData->Distance = $values[$i] / 1000;
                                break;

                            case 'Temperature':
                                $this->Container->WeatherData->Temperature = $values[$i];
                                break;
                        }
                    }
                }

                $this->TmpData = [];
            }

            $this->Container->Metadata->setTimestamp(
                strtotime($startDay.' '.$startTime.' UTC'),
                round((strtotime($startDay.' '.$startTime.' UTC') - strtotime($startDay.' '.$startTime)) / 60)
            );
        }
    }

    /**
     * @param string $line
     */
    protected function readTrainingData($line) {
        if (empty($this->TmpData)) {
            $this->TmpData = explode(',', $line);
        } else {
            $values = explode(',', $line);

            foreach ($this->TmpData as $i => $label) {
                switch ($label) {
                    case 'Calorie':
                        $this->Container->ActivityData->EnergyConsumption = $values[$i];
                        break;
                }
            }

            $this->TmpData = [];
        }
    }

    /**
     * @var string $line
     */
    protected function readGpsData($line) {
        $parts = explode(',', $line);

        if (count($parts) == 2) {
            $this->finishGpsData();

            $this->GraphKey = $parts[0];
            $this->GraphString = $parts[1];
        } else {
            $this->GraphString .= $parts[0];
        }
    }

    protected function finishGpsData()
    {
        if (empty($this->GraphString)) {
            return;
        }

        $values = explode(';', $this->GraphString);

        switch ($this->GraphKey) {
            case 'GpsTime':
                $this->Container->ContinuousData->Time = array_map(function ($value) {
                    $parts = explode(':', $value);
                    return 3600 * $parts[0] + 60 * $parts[1] + $parts[2];
                }, $values);
                break;

            case 'GpsLatitude':
                $this->Container->ContinuousData->Latitude = array_map(function ($value) {
                    return $value / 1000000;
                }, $values);
                break;

            case 'GpsLongitude':
                $this->Container->ContinuousData->Longitude = array_map(function ($value) {
                    return $value / 1000000;
                }, $values);
                break;
        }

        $this->GraphKey = '';
        $this->GraphString = '';
    }

    /**
     * @param string $line
     */
    protected function readGraphData($line)
    {
        $values = explode(',', $line);
        $label = array_shift($values);

        switch ($label) {
            case 'GraphAltitude':
                $this->Container->ContinuousData->Altitude = $values;
                break;

            case 'GraphPitch':
                $this->Container->ContinuousData->Cadence = array_map(function ($value) {
                    return round($value / 2);
                }, $values);
                break;

            case 'GraphDistance':
                $this->Container->ContinuousData->Distance = array_map(function ($value) {
                    return $value / 1000;
                }, $values);
                break;

            case 'HeartRate':
                $this->Container->ContinuousData->HeartRate = $values;
                break;
        }
    }

    /**
     * @param string $line
     */
    protected function readLapData($line)
    {
        if (empty($this->TmpData)) {
            $this->TmpData = [false, false];
            $labels = explode(',', $line);

            foreach ($labels as $i => $label) {
                if ($label == 'LapTime') {
                    $this->TmpData[0] = $i;
                } elseif ($label == 'LapDistance') {
                    $this->TmpData[1] = $i;
                }
            }
        } elseif ($this->TmpData[0] !== false && $this->TmpData[1] !== false) {
            $values = explode(',', $line);

            $this->Container->Rounds->add(
                new Round($values[$this->TmpData[1]] / 1000, $values[$this->TmpData[0]])
            );
        }
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1575
     */
    protected function adjustArraySizes()
    {
        $sizeTimeData = count($this->Container->ContinuousData->Time);

        if ($sizeTimeData > 0) {
            $keysToAdjust = $this->gpsKeysThatExceed($sizeTimeData);
            $newArrays = $this->createNewArraysFor($keysToAdjust);

            foreach ($this->Container->ContinuousData->Time as $sec) {
                foreach ($keysToAdjust as $key) {
                    $newArrays[$key][] = isset($this->Container->ContinuousData->{$key}[$sec]) ? $this->Container->ContinuousData->{$key}[$sec] : 0;
                }
            }

            $this->useNewArrays($newArrays);
        }
    }

    /**
     * @param int $limit
     * @return array
     */
    protected function gpsKeysThatExceed($limit)
    {
        $keys = [];

        foreach ($this->Container->ContinuousData->getPropertyNamesOfArrays() as $key) {
            $num = count($this->Container->ContinuousData->{$key});

            if ($num > $limit) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * @param array $keys
     * @return array
     */
    protected function createNewArraysFor(array $keys)
    {
        $newArrays = array();

        foreach ($keys as $key) {
            $newArrays[$key] = [];
        }

        return $newArrays;
    }

    /**
     * @param array $newArrays
     */
    protected function useNewArrays(array $newArrays)
    {
        foreach ($newArrays as $key => $data) {
            $this->Container->ContinuousData->{$key} = $data;
        }
    }
}
