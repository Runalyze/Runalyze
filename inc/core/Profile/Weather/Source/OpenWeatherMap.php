<?php

namespace Runalyze\Profile\Weather\Source;

use Symfony\Component\Translation\TranslatorInterface;

class OpenWeatherMap extends AbstractSource
{
	public function getInternalProfileEnum()
    {
        return WeatherSourceProfile::OPEN_WEATHER_MAP;
    }

    public function getAttributionLabel(TranslatorInterface $translator)
    {
        return 'openweathermap.org';
    }

    public function getAttributionUrl()
    {
        return 'http://openweathermap.org/';
    }
}
