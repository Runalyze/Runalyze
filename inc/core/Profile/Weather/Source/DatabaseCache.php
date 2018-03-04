<?php

namespace Runalyze\Profile\Weather\Source;

use Symfony\Component\Translation\TranslatorInterface;

class DatabaseCache extends AbstractSource
{
	public function getInternalProfileEnum()
    {
        return WeatherSourceProfile::DATABASE_CACHE;
    }

    public function requiresAttribution()
    {
        return false;
    }

    public function getAttributionLabel(TranslatorInterface $translator)
    {
        return $translator->trans('internal database');
    }
}
