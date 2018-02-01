<?php

namespace Runalyze\Parser\Activity\Common\Data;

use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollectionFiller;
use Runalyze\Parser\Activity\Common\Filter\FilterCollection;

class ActivityDataContainer
{
    /** @var Metadata */
    public $Metadata;

    /** @var ActivityData */
    public $ActivityData;

    /** @var ContinuousData */
    public $ContinuousData;

    /** @var ContinuousDataAdapter */
    protected $ContinuousDataAdapter;

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
        $this->ContinuousDataAdapter = new ContinuousDataAdapter($this->ContinuousData);
        $this->Rounds = new RoundCollection();
        $this->Pauses = new PauseCollection();
        $this->PausesToApply = new PauseCollection();
        $this->FitDetails = new FitDetails();
        $this->WeatherData = new WeatherData();
    }

    public function __clone()
    {
        $this->Metadata = clone $this->Metadata;
        $this->ActivityData = clone $this->ActivityData;
        $this->ContinuousData = clone $this->ContinuousData;
        $this->ContinuousDataAdapter = new ContinuousDataAdapter($this->ContinuousData);
        $this->Rounds = clone $this->Rounds;
        $this->Pauses = clone $this->Pauses;
        $this->PausesToApply = clone $this->PausesToApply;
        $this->FitDetails = clone $this->FitDetails;
        $this->WeatherData = clone $this->WeatherData;
    }

    public function completeContinuousData()
    {
        $this->ContinuousDataAdapter->filterUnwantedZeros();
        $this->ContinuousDataAdapter->clearEmptyArrays();
        $this->ContinuousDataAdapter->calculateDistancesIfRequired();
        $this->ContinuousDataAdapter->correctCadenceIfRequired();

        $this->completeRoundsIfRequired();
        $this->clearRoundsIfOnlyOneRoundIsThere();
        $this->applyPauses();
    }

    public function completeActivityData()
    {
        $this->ActivityData->completeFromContinuousData($this->ContinuousData);
        $this->ActivityData->completeFromRounds($this->Rounds);
        $this->ActivityData->completeFromPauses($this->Pauses);
    }

    public function filterActivityData(FilterCollection $filter)
    {
        $filter->filter($this);
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

    protected function clearRoundsIfOnlyOneRoundIsThere()
    {
        if (!$this->Rounds->isEmpty() && $this->Rounds->count() == 1) {
            $this->Rounds->clear();
        }
    }

    protected function applyPauses()
    {
        if (!$this->PausesToApply->isEmpty() && !empty($this->ContinuousData->Time)) {
            $this->Pauses = $this->ContinuousDataAdapter->applyPauses($this->PausesToApply);
            $this->PausesToApply->clear();

            $this->ContinuousDataAdapter->reIndexArrays();
        }
    }
}
