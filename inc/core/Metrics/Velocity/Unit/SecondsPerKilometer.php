<?php

namespace Runalyze\Metrics\Velocity\Unit;

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
