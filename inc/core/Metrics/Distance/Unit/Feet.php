<?php

namespace Runalyze\Metrics\Distance\Unit;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class Feet extends AbstractDistanceUnit
{
    use UnitConversionByFactorTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'ft';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 3280.84;
    }
}
