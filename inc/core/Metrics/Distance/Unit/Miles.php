<?php

namespace Runalyze\Metrics\Distance\Unit;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class Miles extends AbstractDistanceUnit
{
    use UnitConversionByFactorTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'mi';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 0.621371192;
    }
}
