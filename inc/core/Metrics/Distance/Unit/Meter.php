<?php

namespace Runalyze\Metrics\Distance\Unit;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class Meter extends AbstractDistanceUnit
{
    use UnitConversionByFactorTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'm';
    }

    /**
     * @return int
     */
    public function getFactorFromBaseUnit()
    {
        return 1000;
    }
}
