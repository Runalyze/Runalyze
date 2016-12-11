<?php

namespace Runalyze\Metrics\HeartRate\Unit;

use Runalyze\Metrics\Common\FormattableUnitInterface;

abstract class AbstractHeartRateUnitInPercent extends AbstractHeartRateUnit implements FormattableUnitInterface
{
    /**
     * @return int
     */
    public function getDecimals()
    {
        return 0;
    }
}
