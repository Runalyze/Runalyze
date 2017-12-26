<?php

namespace Runalyze\Parser\Activity\Common\Data\Merge;

use Runalyze\Parser\Activity\Common\Data\WeatherData;

class WeatherDataMerger implements MergerInterface
{
    /** @var WeatherData */
    protected $ResultingData;

    /** @var WeatherData */
    protected $DataToMerge;

    public function __construct(WeatherData $firstData, WeatherData $secondData)
    {
        $this->ResultingData = $firstData;
        $this->DataToMerge = $secondData;
    }

    public function merge()
    {
        foreach ($this->getPropertiesToMerge() as $property) {
            if (null === $this->ResultingData->{$property} || '' === $this->ResultingData->{$property}) {
                $this->ResultingData->{$property} = $this->DataToMerge->{$property};
            }
        }
    }

    /**
     * @return array
     */
    protected function getPropertiesToMerge()
    {
        return [
            'Condition',
            'InternalConditionId',
            'Temperature',
            'WindSpeed',
            'WindDirection',
            'Humidity',
            'AirPressure'
        ];
    }
}
