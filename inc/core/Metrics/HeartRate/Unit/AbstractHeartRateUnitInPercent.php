<?php

namespace Runalyze\Metrics\HeartRate\Unit;

use Runalyze\Metrics\Common\FormattableUnitInterface;

abstract class AbstractHeartRateUnitInPercent extends AbstractHeartRateUnit
{
    /**
     * @return string
     */
    public function getJavaScriptConversion()
    {
        return 'Math.round(d*100)';
    }
}
