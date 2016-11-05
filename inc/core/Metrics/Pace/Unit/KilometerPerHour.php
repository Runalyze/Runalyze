<?php

namespace Runalyze\Metrics\Pace\Unit;

class KilometerPerHour extends AbstractPaceInDecimalFormatUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'km/h';
    }

    /**
     * @return float
     */
    public function getDividendFromBaseUnit()
    {
        return 3600;
    }
}
