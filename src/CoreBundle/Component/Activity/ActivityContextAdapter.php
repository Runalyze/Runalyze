<?php

namespace Runalyze\Bundle\CoreBundle\Component\Activity;

use Runalyze\Bundle\CoreBundle\Services\Import\DuplicateFinder;
use Runalyze\Bundle\CoreBundle\Services\Import\WeatherDataToActivityConverter;
use Runalyze\Bundle\CoreBundle\Services\Import\WeatherForecast;
use Runalyze\Service\WeatherForecast\Location;
use Runalyze\Util\LocalTime;

class ActivityContextAdapter
{
    /** @var ActivityContext */
    protected $Context;

    /** @var WeatherForecast */
    protected $WeatherForecast;

    /** @var DuplicateFinder */
    protected $DuplicateFinder;

    public function __construct(
        ActivityContext $context,
        WeatherForecast $weatherForecast,
        DuplicateFinder $duplicateFinder
    )
    {
        $this->Context = $context;
        $this->WeatherForecast = $weatherForecast;
        $this->DuplicateFinder = $duplicateFinder;
    }

    /**
     * @param object $object
     * @return string
     */
    protected function getStrategyName($object)
    {
        $fullClassName = get_class($object);

        return substr($fullClassName, strrpos($fullClassName, '\\')+1);
    }

    /**
     * @param string $defaultLocationName
     */
    public function guessWeatherConditions($defaultLocationName)
    {
        $location = new Location();
        $location->setLocationName($defaultLocationName);
        $location->setDateTime(new LocalTime($this->Context->getActivity()->getTime()));

        if ($this->Context->hasRoute() && $this->Context->getRoute()->hasGeohashes()) {
            $this->Context->getRoute()->setStartEndGeohashes();

            $location->setGeohash($this->Context->getRoute()->getStartpoint());
        }

        $weather = $this->WeatherForecast->loadForecast($location);

        if (null !== $weather) {
            $converter = new WeatherDataToActivityConverter();
            $converter->setActivityWeatherDataFor($this->Context->getActivity(), $weather);
        }
    }

    /**
     * @return bool
     */
    public function isPossibleDuplicate()
    {
        return $this->DuplicateFinder->isPossibleDuplicate($this->Context->getActivity());
    }
}
