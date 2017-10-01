<?php

namespace Runalyze\Parser\Activity\Common\Data;

use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollectionFiller;

class ActivityDataContainer
{
    /** @var Metadata */
    public $Metadata;

    /** @var ActivityData */
    public $ActivityData;

    /** @var ContinuousData */
    public $ContinuousData;

    /** @var RoundCollection */
    public $Rounds;

    /** @var PauseCollection */
    public $Pauses;

    /** @var PauseCollection */
    public $PausesToApply;

    /** @var FitDetails */
    public $FitDetails;

    /** @var WeatherData */
    public $WeatherData;

    /** @var array */
    public $RRIntervals = [];

    public function __construct()
    {
        $this->Metadata = new Metadata();
        $this->ActivityData = new ActivityData();
        $this->ContinuousData = new ContinuousData();
        $this->Rounds = new RoundCollection();
        $this->Pauses = new PauseCollection();
        $this->PausesToApply = new PauseCollection();
        $this->FitDetails = new FitDetails();
        $this->WeatherData = new WeatherData();
    }

    public function completeActivityData()
    {
        $this->ContinuousData->calculateDistancesIfRequired();

        $this->completeRoundsIfRequired();
        $this->applyPauses();

        $this->ActivityData->completeFromContinuousData($this->ContinuousData);
        $this->ActivityData->completeFromRounds($this->Rounds);
        $this->ActivityData->completeFromPauses($this->Pauses);
    }

    protected function completeRoundsIfRequired()
    {
        if (!$this->Rounds->isEmpty() && !empty($this->ContinuousData->Time) && !empty($this->ContinuousData->Distance)) {
            if ($this->Rounds->getTotalDuration() == 0) {
                (new RoundCollectionFiller($this->Rounds))->fillTimesFromArray(
                    $this->ContinuousData->Time,
                    $this->ContinuousData->Distance
                );
            } elseif ($this->Rounds->getTotalDistance() == 0.0) {
                (new RoundCollectionFiller($this->Rounds))->fillDistancesFromArray(
                    $this->ContinuousData->Time,
                    $this->ContinuousData->Distance
                );
            }
        }
    }

    protected function applyPauses()
    {
        if (!$this->PausesToApply->isEmpty() && !empty($this->ContinuousData->Time)) {
            $this->Pauses = $this->ContinuousData->applyPauses($this->PausesToApply);
            $this->PausesToApply->clear();
        }
    }
}
