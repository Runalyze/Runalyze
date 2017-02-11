<?php

namespace Runalyze\Metrics\Velocity\Unit;

class MilesPerHour extends AbstractPaceInDecimalFormatUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'mph';
    }

    /**
     * @return float
     */
    public function getDividendFromBaseUnit()
    {
        return 3600 * 0.621371192;
    }
}
