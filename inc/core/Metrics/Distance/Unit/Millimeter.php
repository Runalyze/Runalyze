<?php

namespace Runalyze\Metrics\Distance\Unit;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class Millimeter extends AbstractDistanceUnit
{
    use UnitConversionByFactorTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'mm';
    }

    /**
     * @return int
     */
    public function getFactorFromBaseUnit()
    {
        return 1000000;
    }
}
