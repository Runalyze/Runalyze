<?php

namespace Runalyze\Metrics\Velocity\Unit;

class SecondsPer500y extends AbstractPaceInTimeFormatUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '/500y';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 0.5 / 1.0936133;
    }
}
