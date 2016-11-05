<?php

namespace Runalyze\Metrics\Pace\Unit;

class SecondsPerKilometer extends AbstractPaceInTimeFormatUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '/km';
    }

    /**
     * @return int
     */
    public function getFactorFromBaseUnit()
    {
        return 1;
    }
}
