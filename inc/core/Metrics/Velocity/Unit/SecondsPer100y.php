<?php

namespace Runalyze\Metrics\Velocity\Unit;

class SecondsPer100y extends AbstractPaceInTimeFormatUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '/100y';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 0.1 / 1.0936133;
    }
}
