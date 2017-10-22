<?php

namespace Runalyze\Parser\Activity\Common\Data;

use Runalyze\Calculation\Distribution\TrackdataAverages;
use Runalyze\Model\Trackdata;
use Runalyze\Parser\Activity\Bridge\ContinuousDataConverter;
use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;

class ActivityData
{
    /** @var int|null [s] */
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

    /** @var int|null [W] */
    public $AvgPower = null;

    /** @var int|null [W] */
    public $MaxPower = null;

    /** @var int|null [bpm] */
    public $AvgHeartRate = null;

    /** @var int|null [bpm] */
    public $MaxHeartRate = null;

    /** @var int|null [rpm] */
    public $AvgCadence = null;

    /** @var int|null [ms] */
    public $AvgGroundContactTime = null;

    /** @var int|null [%ooL] */
    public $AvgGroundContactBalance = null;

    /** @var int|null [mm] */
    public $AvgVerticalOscillation = null;

    /** @var int|null */
    public $PoolLength = null;

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
            'PoolLength'
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

        $averages = new TrackdataAverages(
            (new ContinuousDataConverter($data))->convertToLegacyTrackdataModel(), [
                Trackdata\Entity::POWER,
                Trackdata\Entity::HEARTRATE,
                Trackdata\Entity::CADENCE,
                Trackdata\Entity::GROUNDCONTACT,
                Trackdata\Entity::VERTICAL_OSCILLATION,
                Trackdata\Entity::GROUNDCONTACT_BALANCE
            ]
        );

        if (null === $this->AvgPower) {
            $this->AvgPower = $averages->average(Trackdata\Entity::POWER);
        }

        if (null === $this->AvgHeartRate) {
            $this->AvgHeartRate = $averages->average(Trackdata\Entity::HEARTRATE);
        }

        if (null === $this->AvgCadence) {
            $this->AvgCadence = $averages->average(Trackdata\Entity::CADENCE);
        }

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
