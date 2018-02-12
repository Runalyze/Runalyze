<?php

namespace Runalyze\Parser\Activity\Common\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\ContinuousData;

class ContinuousDataMerger implements MergerInterface
{
    /** @var ContinuousData */
    protected $ResultingData;

    /** @var ContinuousData */
    protected $DataToMerge;

    public function __construct(ContinuousData $firstData, ContinuousData $secondData)
    {
        $this->ResultingData = $firstData;
        $this->DataToMerge = $secondData;
    }

    /**
     * @param bool $checkForEqualSizes if set an exception is thrown if data are of different length
     */
    public function merge($checkForEqualSizes = true)
    {
        if ($checkForEqualSizes) {
            $this->checkForEqualSizes();
        }

        $this->mergeAltitudeWithPreferringBarometricData();
        $this->mergeAllArrays();
    }

    protected function checkForEqualSizes()
    {
        $firstLength = $this->ResultingData->getLength();
        $secondLength = $this->DataToMerge->getLength();

        if ($firstLength > 0 && $secondLength > 0 && $firstLength != $secondLength) {
            throw new \RuntimeException('Continuous data of different lengths cannot be merged.');
        }
    }

    protected function mergeAltitudeWithPreferringBarometricData()
    {
        if ($this->DataToMerge->IsAltitudeDataBarometric && !empty($this->DataToMerge->Altitude)) {
            $this->ResultingData->Altitude = [];
        }

        if (empty($this->ResultingData->Altitude)) {
            $this->ResultingData->IsAltitudeDataBarometric = $this->DataToMerge->IsAltitudeDataBarometric;
        }
    }

    protected function mergeAllArrays()
    {
        foreach ($this->ResultingData->getPropertyNamesOfArrays() as $property) {
            if (empty($this->ResultingData->{$property})) {
                $this->ResultingData->{$property} = $this->DataToMerge->{$property};
            }
        }
    }
}
