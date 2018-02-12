<?php

namespace Runalyze\Parser\Activity\Common\Data;

use Runalyze\Calculation\Distribution\TrackdataAverages;
use Runalyze\Model\Trackdata;
use Runalyze\Parser\Activity\Bridge\ContinuousDataConverter;
use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;

class ActivityData
{
    /** @var int|float|null [s] */
    public $Duration = null;

    /** @var int|null [s] */
    public $ElapsedTime = null;

    /** @var float|null [km]  */
    public $Distance = null;

    /** @var int|null [m] */
    public $Elevation = null;

    /** @var int|null [m] */
    public $ElevationAscent = null;

    /** @var int|null [m] */
    public $ElevationDescent = null;

    /** @var int|null [kcal] */
    public $EnergyConsumption = null;

    /** @var int|null */
    public $Trimp = null;

    /** @var int|null [6 .. 20] */
    public $RPE = null;

    /** @var int|float|null [W] */
    public $AvgPower = null;

    /** @var int|null [W] */
    public $MaxPower = null;

    /** @var int|float|null [bpm] */
    public $AvgHeartRate = null;

    /** @var int|null [bpm] */
    public $MaxHeartRate = null;

    /** @var int|float|null [rpm] */
    public $AvgCadence = null;

    /** @var int|float|null [ms] */
    public $AvgGroundContactTime = null;

    /** @var int|float|null [%ooL] */
    public $AvgGroundContactBalance = null;

    /** @var int|float|null [mm] */
    public $AvgVerticalOscillation = null;

    /** @var int|float|null [G] */
    public $AvgImpactGsLeft = null;

    /** @var int|float|null [G] */
    public $AvgImpactGsRight = null;

    /** @var int|float|null [G] */
    public $AvgBrakingGsLeft = null;

    /** @var int|float|null [G] */
    public $AvgBrakingGsRight = null;

    /** @var int|null [°] */
    public $AvgFootstrikeTypeLeft = null;

    /** @var int|null [°] */
    public $AvgFootstrikeTypeRight = null;

    /** @var int|null [°] */
    public $AvgPronationExcursionLeft = null;

    /** @var int|null [°] */
    public $AvgPronationExcursionRight = null;

    /** @var int|null */
    public $PoolLength = null;

    /** @var int|null */
    public $TotalStrokes = null;

    /**
     * @return array
     */
    public function getPropertyNames()
    {
        return [
            'Duration',
            'ElapsedTime',
            'Distance',
            'Elevation',
            'ElevationAscent',
            'ElevationDescent',
            'EnergyConsumption',
            'Trimp',
            'RPE',
            'AvgPower',
            'MaxPower',
            'AvgHeartRate',
            'MaxHeartRate',
            'AvgCadence',
            'AvgGroundContactTime',
            'AvgGroundContactBalance',
            'AvgVerticalOscillation',
            'AvgImpactGsLeft',
            'AvgImpactGsRight',
            'AvgBrakingGsLeft',
            'AvgBrakingGsRight',
            'AvgFootstrikeTypeLeft',
            'AvgFootstrikeTypeRight',
            'AvgPronationExcursionLeft',
            'AvgPronationExcursionRight',
            'PoolLength',
            'TotalStrokes'
        ];
    }

    public function completeFromContinuousData(ContinuousData $data)
    {
        $this->completeTotalValuesFromContinuousData($data);
        $this->completeMaximalValuesFromContinuousData($data);
        $this->completeAverageValuesFromContinuousData($data);
    }

    public function completeTotalValuesFromContinuousData(ContinuousData $data)
    {
        if (null === $this->Duration) {
            $this->Duration = $data->getTotalDuration();
        }

        if (null === $this->Distance) {
            $this->Distance = $data->getTotalDistance();
        }
    }

    public function completeMaximalValuesFromContinuousData(ContinuousData $data)
    {
        if (null === $this->MaxHeartRate) {
            $this->MaxHeartRate = $data->getMaximalHeartRate();
        }

        if (null === $this->MaxPower) {
            $this->MaxPower = $data->getMaximalPower();
        }
    }

    public function completeAverageValuesFromContinuousData(ContinuousData $data)
    {
        if (empty($data->Time)) {
            return;
        }

        try {
            $averages = new TrackdataAverages(
                (new ContinuousDataConverter($data))->convertToLegacyTrackdataModel(), [
                    Trackdata\Entity::POWER,
                    Trackdata\Entity::HEARTRATE,
                    Trackdata\Entity::CADENCE,
                    Trackdata\Entity::GROUNDCONTACT,
                    Trackdata\Entity::VERTICAL_OSCILLATION,
                    Trackdata\Entity::GROUNDCONTACT_BALANCE,
                    Trackdata\Entity::IMPACT_GS_LEFT,
                    Trackdata\Entity::IMPACT_GS_RIGHT,
                    Trackdata\Entity::BRAKING_GS_LEFT,
                    Trackdata\Entity::BRAKING_GS_RIGHT,
                    Trackdata\Entity::FOOTSTRIKE_TYPE_LEFT,
                    Trackdata\Entity::FOOTSTRIKE_TYPE_RIGHT,
                    Trackdata\Entity::PRONATION_EXCURSION_LEFT,
                    Trackdata\Entity::PRONATION_EXCURSION_RIGHT
                ]
            );
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $this->completeStandardAverageValuesFromAverages($averages);
        $this->completeRunningDynamicsAverageValuesFromAverages($averages);
        $this->completeRunScribeDataAverageValuesFromAverages($averages);
    }

    public function completeStandardAverageValuesFromAverages(TrackdataAverages $averages)
    {
        if (null === $this->AvgPower) {
            $this->AvgPower = $averages->average(Trackdata\Entity::POWER);
        }

        if (null === $this->AvgHeartRate) {
            $this->AvgHeartRate = $averages->average(Trackdata\Entity::HEARTRATE);
        }

        if (null === $this->AvgCadence) {
            $this->AvgCadence = $averages->average(Trackdata\Entity::CADENCE);
        }
    }

    public function completeRunningDynamicsAverageValuesFromAverages(TrackdataAverages $averages)
    {
        if (null === $this->AvgGroundContactTime) {
            $this->AvgGroundContactTime = $averages->average(Trackdata\Entity::GROUNDCONTACT);
        }

        if (null === $this->AvgVerticalOscillation) {
            $this->AvgVerticalOscillation = $averages->average(Trackdata\Entity::VERTICAL_OSCILLATION);
        }

        if (null === $this->AvgGroundContactBalance) {
            $this->AvgGroundContactBalance = $averages->average(Trackdata\Entity::GROUNDCONTACT_BALANCE);
        }
    }

    public function completeRunScribeDataAverageValuesFromAverages(TrackdataAverages $averages)
    {
        if (null === $this->AvgImpactGsLeft) {
            $this->AvgImpactGsLeft = $averages->average(Trackdata\Entity::IMPACT_GS_LEFT);
        }

        if (null === $this->AvgImpactGsRight) {
            $this->AvgImpactGsRight = $averages->average(Trackdata\Entity::IMPACT_GS_RIGHT);
        }

        if (null === $this->AvgBrakingGsLeft) {
            $this->AvgBrakingGsLeft = $averages->average(Trackdata\Entity::BRAKING_GS_LEFT);
        }

        if (null === $this->AvgBrakingGsRight) {
            $this->AvgBrakingGsRight = $averages->average(Trackdata\Entity::BRAKING_GS_RIGHT);
        }

        if (null === $this->AvgFootstrikeTypeLeft) {
            $this->AvgFootstrikeTypeLeft = $averages->average(Trackdata\Entity::FOOTSTRIKE_TYPE_LEFT);
        }

        if (null === $this->AvgFootstrikeTypeRight) {
            $this->AvgFootstrikeTypeRight = $averages->average(Trackdata\Entity::FOOTSTRIKE_TYPE_RIGHT);
        }

        if (null === $this->AvgPronationExcursionLeft) {
            $this->AvgPronationExcursionLeft = $averages->average(Trackdata\Entity::PRONATION_EXCURSION_LEFT);
        }

        if (null === $this->AvgPronationExcursionRight) {
            $this->AvgPronationExcursionRight = $averages->average(Trackdata\Entity::PRONATION_EXCURSION_RIGHT);
        }
    }

    public function completeFromPauses(PauseCollection $pauses)
    {
        if (null === $this->ElapsedTime && null !== $this->Duration) {
            $this->ElapsedTime = $this->Duration + $pauses->getTotalDuration();
        }
    }

    public function completeFromRounds(RoundCollection $rounds)
    {
        if (!$rounds->isEmpty()) {
            if (null === $this->Duration) {
                $this->Duration = $rounds->getTotalDuration();
            }

            if (null === $this->Distance) {
                $this->Distance = $rounds->getTotalDistance();
            }
        }
    }
}
