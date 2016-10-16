<?php

namespace Runalyze\Profile\Weather\Mapping;

use Runalyze\Profile\Mapping\ToInternalMappingInterface;
use Runalyze\Profile\Weather\WeatherConditionProfile;

class OpenWeatherMapMapping implements ToInternalMappingInterface
{
    /**
     * @see http://openweathermap.org/weather-conditions
     *
     * @param int|string $value
     * @return int|string
     */
    public function toInternal($value)
    {
        switch ($value) {
            case 800:
                return WeatherConditionProfile::SUNNY;
            case 801:
                return WeatherConditionProfile::FAIR;
            case 200:
            case 210:
            case 211:
            case 212:
            case 221:
            case 230:
            case 231:
            case 232:
                return WeatherConditionProfile::THUNDERSTORM;
            case 300:
            case 301:
            case 802:
            case 701:
            case 711:
            case 721:
            case 731:
            case 741:
                return WeatherConditionProfile::CHANGEABLE;
            case 803:
            case 804:
                return WeatherConditionProfile::CLOUDY;
            case 502:
            case 503:
            case 504:
            case 521:
            case 522:
            case 531:
                return WeatherConditionProfile::HEAVYRAIN;
            case 500:
            case 501:
            case 511:
            case 520:
            case 302:
            case 310:
            case 311:
            case 312:
            case 321:
            case 201:
            case 202:
                return WeatherConditionProfile::RAINY;
            case 600:
            case 601:
            case 602:
            case 611:
            case 621:
                return WeatherConditionProfile::SNOWING;
            default:
                return WeatherConditionProfile::UNKNOWN;
        }
    }
}
