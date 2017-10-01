<?php

namespace Runalyze\Parser\Activity\Common\Data;

use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;

class ContinuousData
{
    /** @var array [s] */
    public $Time = [];

    /** @var array [km] */
    public $Distance = [];

    /** @var array [°] */
    public $Latitude = [];

    /** @var array [°] */
    public $Longitude = [];

    /** @var array [m] */
    public $Altitude = [];

    /** @var bool|null */
    public $IsAltitudeDataBarometric = null;

    /** @var array [bpm] */
    public $HeartRate = [];

    /** @var array [rpm] */
    public $Cadence = [];

    /** @var array [W] */
    public $Power = [];

    /** @var array [°C] */
    public $Temperature = [];

    /** @var array [ms] */
    public $GroundContactTime = [];

    /** @var array [mm] */
    public $VerticalOscillation = [];

    /** @var array [%ooL] */
    public $GroundContactBalance = [];

    /** @var array [%SmO2] */
    public $MuscleOxygenation = [];

    /** @var array [%SmO2] */
    public $MuscleOxygenation_2 = [];

    /** @var array [100 * g/dL] */
    public $TotalHaemoglobin = [];

    /** @var array [100 * g/dL] */
    public $TotalHaemoglobin_2 = [];

    /** @var array [-] */
    public $Strokes = [];

    /**
     * @var array
     *
     * @see \Runalyze\Profile\FitSdk\StrokeTypeProfile
     */
    public $StrokeType = [];

    /**
     * @return array
     */
    public function getPropertyNamesOfArrays()
    {
        return [
            'Time',
            'Distance',
            'Latitude',
            'Longitude',
            'Altitude',
            'HeartRate',
            'Cadence',
            'Power',
            'Temperature',
            'GroundContactTime',
            'VerticalOscillation',
            'GroundContactBalance',
            'MuscleOxygenation',
            'MuscleOxygenation_2',
            'TotalHaemoglobin',
            'TotalHaemoglobin_2',
            'Strokes'
        ];
    }

    /**
     * @return int|null [s]
     */
    public function getTotalDuration()
    {
        if (!empty($this->Time)) {
            return end($this->Time);
        }

        return null;
    }

    /**
     * @return float|null [km]
     */
    public function getTotalDistance()
    {
        if (!empty($this->Distance)) {
            return end($this->Distance);
        }

        return null;
    }

    /**
     * @return int|null [bpm]
     */
    public function getMaximalHeartRate()
    {
        if (!empty($this->HeartRate)) {
            return max($this->HeartRate);
        }

        return null;
    }

    /**
     * @return int|null [W]
     */
    public function getMaximalPower()
    {
        if (!empty($this->Power)) {
            return max($this->Power);
        }

        return null;
    }

    public function calculateDistancesIfRequired()
    {
        if ($this->distancesShouldBeCalculated()) {
            (new GpsDistanceCalculator())->calculateDistancesFor($this);
        }
    }

    /**
     * @return bool
     */
    protected function distancesShouldBeCalculated()
    {
        return (
            empty($this->Distance) &&
            !empty($this->Latitude) &&
            !empty($this->Longitude)
        );
    }

    /**
     * @param PauseCollection $pausesToApply
     * @return PauseCollection
     */
    public function applyPauses(PauseCollection $pausesToApply)
    {
        $resultingPauses = new PauseCollection();

        $num = count($this->Time);
        $numPauses = $pausesToApply->count();
        $keys = $this->getPropertyNamesOfArrays();
        $hasHeartRate = !empty($this->HeartRate);
        $hrStart = null;
        $pauseInSeconds = 0;
        $pauseIndex = 0;
        $pauseUntil = 0;
        $pauseTime = $pausesToApply[$pauseIndex]->getTimeIndex();
        $isPause = false;

        for ($i = 0; $i < $num; $i++) {
            if (!$isPause && $this->Time[$i] > $pauseTime) {
                if ($pauseIndex < $numPauses) {
                    $isPause = true;
                    $hrStart = !$hasHeartRate ? null : (isset($this->HeartRate[$i - 1]) ? $this->HeartRate[$i - 1] : $this->HeartRate[$i]);
                    $pauseInSeconds += $pausesToApply[$pauseIndex]->getDuration();
                    $pauseUntil = $pausesToApply[$pauseIndex]->getDuration() + $pausesToApply[$pauseIndex]->getTimeIndex();
                    $pauseIndex++;
                    $pauseTime = ($pauseIndex < $numPauses) ? $pausesToApply[$pauseIndex]->getTimeIndex() : PHP_INT_MAX;
                }
            }

            if ($isPause && $this->Time[$i] >= $pauseUntil) {
                $isPause = false;
                $newPause = clone $pausesToApply[$pauseIndex - 1];
                $newPause->setHeartRateDetails($hrStart, $hasHeartRate ? $this->HeartRate[$i] : null);

                $resultingPauses->add($newPause);
            }

            if ($isPause) {
                foreach ($keys as $key) {
                    if (isset($this->{$key}[$i])) {
                        unset($this->{$key}[$i]);
                    }
                }
            } else {
                $this->Time[$i] -= $pauseInSeconds;
            }
        }

        return $resultingPauses;
    }
}
