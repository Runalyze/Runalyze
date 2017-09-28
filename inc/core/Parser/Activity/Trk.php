<?php

namespace Runalyze\Parser\Activity;

use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileContentAwareParserTrait;

class Trk extends AbstractSingleParser implements FileContentAwareParserInterface
{
    use FileContentAwareParserTrait;

    /** @var bool */
    protected $IsStarted = false;

    /** @var bool */
    protected $IsPaused = false;

    /** @var int */
    protected $StartTime = 0;

    /** @var int */
    protected $PauseInSeconds = 0;

    public function parse()
    {
        $separator = "\r\n";
        $line = strtok($this->FileContent, $separator);

        while ($line !== false) {
            $this->parseLine($line);
            $line = strtok($separator);
        }

        $this->finishData();
    }

    protected function finishData()
    {
        $this->Container->Metadata->setTimestamp(
            $this->StartTime,
            round((new \DateTime())->setTimestamp($this->StartTime)->getOffset() / 60)
        );
    }

    /**
     * @param string $line
     */
    protected function parseLine($line)
    {
        $firstChar = substr($line, 0, 1);

        switch ($firstChar) {
            case 'T':
                $this->readTrackPoint($line);
                break;
        }
    }

    /**
     * @param string $line
     */
    protected function readTrackPoint($line)
    {
        $values = preg_split('/[\s]+/', $line);
        $num = count($values);

        if ($num < 7) {
            return;
        }

        $latitude = floatval($values[2]);
        $longitude = floatval($values[3]);
        $time = strtotime($values[4].' '.$values[5].' UTC') - $this->StartTime - $this->PauseInSeconds;

        if (!$this->IsStarted) {
            $this->IsStarted = true;
            $this->StartTime = $time;
            $time = 0;
        }

        if ('N' == $values[6] && $time > 0) {
            $this->IsPaused = true;

            if ($time == end($this->Container->ContinuousData->Time)) {
                return;
            }
        } elseif ($this->IsPaused) {
            $this->IsPaused = false;

            if ($time > 0) {
                $currentPause = $time - end($this->Container->ContinuousData->Time);
                $this->PauseInSeconds += $currentPause;

                $this->Container->Pauses->add(new Pause(
                    end($this->Container->ContinuousData->Time),
                    $currentPause
                ));
            }

            return;
        }

        $this->Container->ContinuousData->Time[] = $time;
        $this->Container->ContinuousData->Latitude[] = $latitude;
        $this->Container->ContinuousData->Longitude[] = $longitude;
        $this->Container->ContinuousData->Altitude[] = ($num > 7 && $values[7] != '-1') ? round($values[7]) : 0;
        $this->Container->ContinuousData->Temperature[] = ($num > 14 && $values[14] != '-1') ? round($values[14]) : 0;
        $this->Container->ContinuousData->HeartRate[] = ($num > 17 && $values[17] != '-1') ? round($values[17]) : 0;
    }
}
