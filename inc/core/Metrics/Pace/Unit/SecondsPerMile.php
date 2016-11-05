<?php

namespace Runalyze\Metrics\Pace\Unit;

class SecondsPerMile extends AbstractPaceInTimeFormatUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '/mi';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 1.0 / 0.621371192;
    }
}
