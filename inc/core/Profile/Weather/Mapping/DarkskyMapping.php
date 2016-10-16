<?php

namespace Runalyze\Profile\Weather\Mapping;

use Runalyze\Profile\Mapping\ToInternalMappingInterface;
use Runalyze\Profile\Weather\WeatherConditionProfile;

class DarkskyMapping implements ToInternalMappingInterface
{
    /**
     * @see https://darksky.net/dev/docs/response
     *
     * @param int|string $value
     * @return int|string
     */
    public function toInternal($value)
    {
        switch ($value) {
            case 'clear-day':
            case 'clear-night':
                return WeatherConditionProfile::FAIR;
            case 'wind':
                return WeatherConditionProfile::WINDY;
            case 'fog':
                return WeatherConditionProfile::FOGGY;
            case 'partly-cloudy-night':
            case 'partly-cloudy-day':
                return WeatherConditionProfile::CHANGEABLE;
            case 'cloudy':
                return WeatherConditionProfile::CLOUDY;
            case 'rain':
                return WeatherConditionProfile::RAINY;
            case 'snow':
            case 'sleet':
                return WeatherConditionProfile::SNOWING;
            default:
                return WeatherConditionProfile::UNKNOWN;
        }
    }
}
