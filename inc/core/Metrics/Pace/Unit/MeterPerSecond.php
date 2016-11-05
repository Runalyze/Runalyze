<?php

namespace Runalyze\Metrics\Pace\Unit;

class MeterPerSecond extends AbstractPaceInDecimalFormatUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'm/s';
    }

    /**
     * @return float
     */
    public function getDividendFromBaseUnit()
    {
        return 1000;
    }
}
