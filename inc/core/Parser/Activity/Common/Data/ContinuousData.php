<?php

namespace Runalyze\Parser\Activity\Common\Data;

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

    /** @var float[] [G] */
    public $ImpactGsLeft = [];

    /** @var float[] [G] */
    public $ImpactGsRight = [];

    /** @var float[] [G] */
    public $BrakingGsLeft = [];

    /** @var float[] [G] */
    public $BrakingGsRight = [];

    /** @var int[] [°] */
    public $FootstrikeTypeLeft = [];

    /** @var int[] [°] */
    public $FootstrikeTypeRight = [];

    /** @var float[] [°] */
    public $PronationExcursionLeft = [];

    /** @var float[] [°] */
    public $PronationExcursionRight = [];

    /** @var array [%ooL] */
    public $LeftRightBalance = [];

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
            'ImpactGsLeft',
            'ImpactGsRight',
            'BrakingGsLeft',
            'BrakingGsRight',
            'FootstrikeTypeLeft',
            'FootstrikeTypeRight',
            'PronationExcursionLeft',
            'PronationExcursionRight',
            'LeftRightBalance',
            'Strokes',
            'StrokeType'
        ];
    }

    /**
     * @return array
     */
    public function getPropertyNamesOfArraysThatShouldNotContainZeros()
    {
        return [
            'HeartRate'
        ];
    }

    /**
     * @return int size of first non-empty array property
     */
    public function getLength()
    {
        foreach ($this->getPropertyNamesOfArrays() as $property) {
            if (!empty($this->{$property})) {
                return count($this->{$property});
            }
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        foreach ($this->getPropertyNamesOfArrays() as $property) {
            if (!empty($this->{$property})) {
                return false;
            }
        }

        return true;
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
}
