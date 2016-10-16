<?php

namespace Runalyze\Profile\Weather\Mapping;

use Runalyze\Profile\Mapping\ToInternalMappingInterface;
use Runalyze\Profile\Weather\WeatherConditionProfile;

/**
 * This mapping was originally used to translate strings from RunningAHEAD backup
 * but can be used for English texts in general without problems.
 */
class EnglishTextMapping implements ToInternalMappingInterface
{
    /**
     * @param int|string $value
     * @return int|string
     */
    public function toInternal($value)
    {
        switch (strtolower($value)) {
            case 'mostly sunny':
            case 'sunny':
            case 'clear':
                return WeatherConditionProfile::SUNNY;

            case 'partly sunny':
            case 'partly cloudy':
                return WeatherConditionProfile::FAIR;

            case 'overcast':
            case 'mostly cloudy':
            case 'cloudy':
                return WeatherConditionProfile::CLOUDY;

            case 'fog':
                return WeatherConditionProfile::FOGGY;

            case 'mist':
            case 'chance of rain':
            case 'drizzle':
                return WeatherConditionProfile::CHANGEABLE;

            case 'rain':
            case 'light rain':
            case 'rain and snow':
            case 'freezing drizzle':
            case 'sleet':
                return WeatherConditionProfile::RAINY;

            case 'scattered showers':
            case 'showers':
                return WeatherConditionProfile::HEAVYRAIN;

            case 'storm':
            case 'windy':
                return WeatherConditionProfile::WINDY;

            case 'chance of tstorm':
            case 'scattered thunderstorms':
            case 'thunderstorm':
                return WeatherConditionProfile::THUNDERSTORM;

            case 'haze':
            case 'flurries':
            case 'icy':
            case 'snow':
            case 'light snow':
            case 'chance of snow':
            case 'scattered snow showers':
                return WeatherConditionProfile::SNOWING;

            default:
                return WeatherConditionProfile::UNKNOWN;
        }
    }
}
