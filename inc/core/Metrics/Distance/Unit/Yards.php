<?php

namespace Runalyze\Metrics\Distance\Unit;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class Yards extends AbstractDistanceUnit
{
    use UnitConversionByFactorTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'yd';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 1093.6133;
    }
}
