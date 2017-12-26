<?php

namespace Runalyze\Profile\Weather\Source;

use Symfony\Component\Translation\TranslatorInterface;

class DarkSky extends AbstractSource
{
	public function getInternalProfileEnum()
    {
        return WeatherSourceProfile::DARK_SKY;
    }

    public function getAttributionLabel(TranslatorInterface $translator)
    {
        return 'Powered by Dark Sky';
    }

    public function getAttributionUrl()
    {
        return 'https://darksky.net/poweredby/';
    }
}
