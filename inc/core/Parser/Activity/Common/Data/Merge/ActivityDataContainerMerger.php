<?php

namespace Runalyze\Parser\Activity\Common\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

class ActivityDataContainerMerger
{
    /** @var ActivityDataContainer */
    protected $ResultingContainer;

    public function __construct(ActivityDataContainer $firstContainer, ActivityDataContainer $secondContainer)
    {
        $this->ResultingContainer = clone $firstContainer;

        $this->mergeContainerIntoResult($secondContainer);
    }

    protected function mergeContainerIntoResult(ActivityDataContainer $container)
    {
        $this->forwardMergingToClassesFor($container);
        $this->copyPropertiesIfEmptyFrom($container);
    }

    protected function forwardMergingToClassesFor(ActivityDataContainer $container)
    {
        (new MetadataMerger($this->ResultingContainer->Metadata, $container->Metadata))->merge();
        (new ActivityDataMerger($this->ResultingContainer->ActivityData, $container->ActivityData))->merge();
        (new ContinuousDataMerger($this->ResultingContainer->ContinuousData, $container->ContinuousData))->merge();
        (new FitDetailsMerger($this->ResultingContainer->FitDetails, $container->FitDetails))->merge();
        (new WeatherDataMerger($this->ResultingContainer->WeatherData, $container->WeatherData))->merge();
    }

    protected function copyPropertiesIfEmptyFrom(ActivityDataContainer $container)
    {
        if ($this->ResultingContainer->Rounds->isEmpty() && !$container->Rounds->isEmpty()) {
            $this->ResultingContainer->Rounds = clone $container->Rounds;
        }

        if ($this->ResultingContainer->Pauses->isEmpty() && !$container->Pauses->isEmpty()) {
            $this->ResultingContainer->Pauses = clone $container->Pauses;
        }

        if (empty($this->ResultingContainer->RRIntervals)) {
            $this->ResultingContainer->RRIntervals = $container->RRIntervals;
        }
    }
    /**
     * @return ActivityDataContainer
     */
    public function getResultingContainer()
    {
        return $this->ResultingContainer;
    }
}
