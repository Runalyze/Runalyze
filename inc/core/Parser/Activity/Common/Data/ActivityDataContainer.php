<?php

namespace Runalyze\Parser\Activity\Common\Data;

use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;

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
        $this->FitDetails = new FitDetails();
        $this->WeatherData = new WeatherData();
    }

    public function completeActivityData()
    {
        $this->ContinuousData->calculateDistancesIfRequired();

        $this->ActivityData->completeFromContinuousData($this->ContinuousData);
        $this->ActivityData->completeFromRounds($this->Rounds);
        $this->ActivityData->completeFromPauses($this->Pauses);
    }
}
