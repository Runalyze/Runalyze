<?php

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Model\Trackdata\Pause;

class ParserCSVwahooSingle extends ParserAbstractSingle
{
	/** @var string */
	const NEWLINE = "\n";

    /** @var string first column label of current block */
    protected $CurrentBlock = '';

    /** @var array column labels for current block */
    protected $CurrentHeader = [];

    /** @var array values in current row */
    protected $Row = [];

    /** @var array [[dist, duration], ...] */
    protected $TempSplits = [];

    /** @var int [s] */
    protected $PauseTime = 0;

    /** @var int [s] */
    protected $PauseStart = 0;

    /** @var bool */
    protected $IsPaused = false;

	public function parse()
    {
		$line = strtok($this->FileContent, self::NEWLINE);

		while ($line !== false) {
            $this->parseLine(trim($line));
			$line = strtok(self::NEWLINE);
		}

        $this->setSplits();
		$this->setGPSarrays();
	}

	/**
	 * @param string $line
	 */
	protected function parseLine($line)
    {
        $this->Row = explode(',', $line);

        if (in_array($this->Row[0], ['Device', 'interval', 'time'])) {
            $this->setHeader($this->Row);
		} else {
            switch ($this->CurrentBlock) {
                case 'Device':
                    $this->parseDeviceLine();
                    break;
                case 'interval':
                    $this->parseIntervalLine();
                    break;
                case 'time':
                    $this->parseTrackLine();
                    break;
                default:
                    return;
            }
        }
	}

    protected function clearHeader()
    {
        $this->CurrentBlock = '';
        $this->CurrentHeader = [];
    }

    /**
     * @param array $columnLabels
     *
     * @throws UnsupportedFileException
     */
    protected function setHeader(array $columnLabels)
    {
        $this->CurrentBlock = $columnLabels[0];
        $this->CurrentHeader = array_flip($columnLabels);

        if ('interval' == $this->CurrentBlock) {
            if (!isset($this->CurrentHeader['runningtime'])) {
                throw new UnsupportedFileException('\'interval\' rows must specify at least \'runningtime\'.');
            }
        } elseif ('time' == $this->CurrentBlock) {
            if (!isset($this->CurrentHeader['paused'])) {
                throw new UnsupportedFileException('\'time\' rows must specify at least \'paused\'.');
            }
        }
    }

    protected function parseDeviceLine()
    {
        if (
            'Year' == $this->Row[0] &&
            'Month' == $this->Row[2] &&
            'Day' == $this->Row[4] &&
            'Hour' == $this->Row[6] &&
            'Minute' == $this->Row[8] &&
            'Second' == $this->Row[10]
        ) {
            $this->TrainingObject->setTimestamp(
                mktime(
                    (int)$this->Row[7],
                    (int)$this->Row[9],
                    (int)$this->Row[11],
                    (int)$this->Row[3],
                    (int)$this->Row[5],
                    (int)$this->Row[1]
                )
            );
            $this->interpretTimestampAsServerTime();
        }
    }

    protected function parseIntervalLine()
    {
        if (count($this->TempSplits) < (int)$this->Row[0]) {
            $this->TempSplits[] = [
                $this->currentValue(['gpsdist', 'wheeldist', 'stridedist', 'manualdist']) / 1000,
                (int)$this->Row[$this->CurrentHeader['runningtime']]
            ];
        }
    }

    protected function setSplits()
    {
        $splitsObject = $this->TrainingObject->Splits();

        foreach ($this->TempSplits as $split) {
            $splitsObject->addSplit($split[0], $split[1]);
        }
    }

    protected function parseTrackLine()
    {
        if (!$this->IsPaused) {
            if ('1' == $this->Row[$this->CurrentHeader['paused']]) {
                $this->startNewPause();
            } else {
                $this->parseActiveTrackLine();
            }
        } else {
            if ('0' == $this->Row[$this->CurrentHeader['paused']]) {
                $this->finishCurrentPause();
                $this->parseActiveTrackLine();
            }
        }
    }

    protected function parseActiveTrackLine()
    {
        $this->gps['time_in_s'][] = (int)$this->Row[0] - $this->PauseTime;
        $this->gps['km'][] = round($this->currentValue(['gps_dist', 'manual_dist', 'pwr_accdist', 'spd_accdist', 'fp_accdist']) / 1000, ParserAbstract::DISTANCE_PRECISION);
        $this->gps['latitude'][] = $this->currentValue(['gps_lat']);
        $this->gps['longitude'][] = $this->currentValue(['gps_lon']);
        $this->gps['altitude'][] = (int)$this->currentValue(['disp_altitude', 'gps_altitude']);
        $this->gps['heartrate'][] = (int)$this->currentValue(['hr_heartrate']);
        $this->gps['power'][] = (int)$this->currentValue(['pwr_instpwr']);
        $this->gps['rpm'][] = (int)($this->currentValue(['cad_cadence', 'ma_cadence', 'pwr_cadence']) / 2); // stored in [spm]
        $this->gps['groundcontact'][] = (int)($this->currentValue(['ma_gct']) * 1000); // stored in [s]
        $this->gps['oscillation'][] = (int)($this->currentValue(['ma_vertosc']) * 1000); // stored in [m]
        $this->gps['temp'][] = (int)$this->currentValue(['disp_temperature']);
    }

    protected function startNewPause()
    {
        if (!empty($this->gps['time_in_s'])) {
            $this->IsPaused = true;
            $this->PauseStart = (int)$this->Row[0];
        }
    }

    protected function finishCurrentPause()
    {
        if (!empty($this->gps['time_in_s'])) {
            $lastTime = end($this->gps['time_in_s']);

            $this->IsPaused = false;
            $this->PauseTime += (int)$this->Row[0] - $this->PauseStart;

            $this->TrainingObject->Pauses()->add(
                new Pause(
                    $lastTime,
                    (int)$this->Row[0] - $this->PauseStart,
                    end($this->gps['heartrate']),
                    isset($this->Row[$this->CurrentHeader['hr_heartrate']]) ? (int)$this->Row[$this->CurrentHeader['hr_heartrate']] : 0
                )
            );
        }
    }

    /**
     * Get current value that may come from different rows.
     * Distance for example can come from 'gpsdist' or 'manualdist'.
     *
     * @param string[] $possibleColumns ordered column names to look for
     * @return float 0.0 is returned if no column could be found
     */
    protected function currentValue(array $possibleColumns)
    {
        foreach ($possibleColumns as $columnName) {
            if (isset($this->CurrentHeader[$columnName]) && 0.0 != (float)$this->Row[$this->CurrentHeader[$columnName]]) {
                return (float)$this->Row[$this->CurrentHeader[$columnName]];
            }
        }

        return 0.0;
    }
}
