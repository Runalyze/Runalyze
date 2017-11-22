<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityContext;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityContextAdapter;

class ActivityContextAdapterFactory
{
    /** @var WeatherForecast */
    protected $WeatherForecast;

    /** @var DuplicateFinder */
    protected $DuplicateFinder;

    public function __construct(
        WeatherForecast $weatherForecast,
        DuplicateFinder $duplicateFinder
    )
    {
        $this->WeatherForecast = $weatherForecast;
        $this->DuplicateFinder = $duplicateFinder;
    }

    public function getAdapterFor(ActivityContext $context)
    {
        return new ActivityContextAdapter(
            $context,
            $this->WeatherForecast,
            $this->DuplicateFinder
        );
    }
}
