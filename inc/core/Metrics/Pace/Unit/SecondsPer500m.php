<?php

namespace Runalyze\Metrics\Pace\Unit;

class SecondsPer500m extends AbstractPaceInTimeFormatUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '/500m';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 0.5;
    }
}
