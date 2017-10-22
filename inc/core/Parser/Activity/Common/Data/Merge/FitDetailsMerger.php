<?php

namespace Runalyze\Parser\Activity\Common\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\FitDetails;

class FitDetailsMerger implements MergerInterface
{
    /** @var FitDetails */
    protected $ResultingDetails;

    /** @var FitDetails */
    protected $DetailsToMerge;

    public function __construct(FitDetails $firstDetails, FitDetails $secondDetails)
    {
        $this->ResultingDetails = $firstDetails;
        $this->DetailsToMerge = $secondDetails;
    }

    public function merge()
    {
        foreach ($this->getPropertiesToMerge() as $property) {
            if (null === $this->ResultingDetails->{$property}) {
                $this->ResultingDetails->{$property} = $this->DetailsToMerge->{$property};
            }
        }
    }

    /**
     * @return array
     */
    protected function getPropertiesToMerge()
    {
        return [
            'VO2maxEstimate',
            'RecoveryTime',
            'HrvAnalysis',
            'TrainingEffect',
            'PerformanceCondition',
            'PerformanceConditionEnd'
        ];
    }
}
