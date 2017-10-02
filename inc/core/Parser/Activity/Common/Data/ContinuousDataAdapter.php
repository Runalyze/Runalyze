<?php

namespace Runalyze\Parser\Activity\Common\Data;

use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;

class ContinuousDataAdapter
{
    /** @var int */
    const RPM_LIMIT_FOR_CORRECTION = 130;

    /** @var ContinuousData */
    protected $ContinuousData;

    public function __construct(ContinuousData $continuousData)
    {
        $this->ContinuousData = $continuousData;
    }

    public function calculateDistancesIfRequired()
    {
        if ($this->distancesShouldBeCalculated()) {
            (new GpsDistanceCalculator())->calculateDistancesFor($this->ContinuousData);
        }
    }

    /**
     * @return bool
     */
    protected function distancesShouldBeCalculated()
    {
        return (
            empty($this->ContinuousData->Distance) &&
            !empty($this->ContinuousData->Latitude) &&
            !empty($this->ContinuousData->Longitude)
        );
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1367
     */
    public function correctCadenceIfRequired()
    {
        if (!empty($this->ContinuousData->Cadence)) {
            $avg = array_sum($this->ContinuousData->Cadence) / count($this->ContinuousData->Cadence);

            if ($avg > self::RPM_LIMIT_FOR_CORRECTION) {
                $this->ContinuousData->Cadence = array_map(function ($v) {
                    return round($v / 2);
                }, $this->ContinuousData->Cadence);
            }
        }
    }

    public function clearEmptyArrays()
    {
        $arrayKeys = $this->ContinuousData->getPropertyNamesOfArrays();
        $arrayKeysWithData = [];
        $arraySize = null;

        foreach ($arrayKeys as $key) {
            if (!empty($this->ContinuousData->{$key})) {
                $arrayKeysWithData[] = $key;

                if (null === $arraySize) {
                    $arraySize = count($this->ContinuousData->{$key});
                }
            }
        }

        for ($i = 0; $i < $arraySize; ++$i) {
            if (empty($arrayKeysWithData)) {
                return;
            }

            foreach ($arrayKeysWithData as $j => $key) {
                if (null !== $this->ContinuousData->{$key}[$i]) {
                    unset($arrayKeysWithData[$j]);
                }
            }
        }

        foreach ($arrayKeysWithData as $key) {
            $this->ContinuousData->{$key} = [];
        }
    }

    /**
     * @param PauseCollection $pausesToApply
     * @return PauseCollection
     */
    public function applyPauses(PauseCollection $pausesToApply)
    {
        $resultingPauses = new PauseCollection();

        $num = count($this->ContinuousData->Time);
        $numPauses = $pausesToApply->count();
        $keys = $this->ContinuousData->getPropertyNamesOfArrays();
        $hasHeartRate = !empty($this->ContinuousData->HeartRate);
        $hrStart = null;
        $pauseInSeconds = 0;
        $pauseIndex = 0;
        $pauseUntil = 0;
        $pauseTime = $pausesToApply[$pauseIndex]->getTimeIndex();
        $isPause = false;

        for ($i = 0; $i < $num; $i++) {
            if (!$isPause && $this->ContinuousData->Time[$i] > $pauseTime) {
                if ($pauseIndex < $numPauses) {
                    $isPause = true;
                    $hrStart = !$hasHeartRate ? null : (isset($this->ContinuousData->HeartRate[$i - 1]) ? $this->ContinuousData->HeartRate[$i - 1] : $this->ContinuousData->HeartRate[$i]);
                    $pauseInSeconds += $pausesToApply[$pauseIndex]->getDuration();
                    $pauseUntil = $pausesToApply[$pauseIndex]->getDuration() + $pausesToApply[$pauseIndex]->getTimeIndex();
                    $pauseIndex++;
                    $pauseTime = ($pauseIndex < $numPauses) ? $pausesToApply[$pauseIndex]->getTimeIndex() : PHP_INT_MAX;
                }
            }

            if ($isPause && $this->ContinuousData->Time[$i] >= $pauseUntil) {
                $isPause = false;
                $newPause = clone $pausesToApply[$pauseIndex - 1];
                $newPause->setHeartRateDetails($hrStart, $hasHeartRate ? $this->ContinuousData->HeartRate[$i] : null);

                $resultingPauses->add($newPause);
            }

            if ($isPause) {
                foreach ($keys as $key) {
                    if (isset($this->ContinuousData->{$key}[$i])) {
                        unset($this->ContinuousData->{$key}[$i]);
                    }
                }
            } else {
                $this->ContinuousData->Time[$i] -= $pauseInSeconds;
            }
        }

        return $resultingPauses;
    }
}
