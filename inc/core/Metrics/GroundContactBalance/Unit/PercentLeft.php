<?php

namespace Runalyze\Metrics\GroundContactBalance\Unit;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class PercentLeft extends AbstractGroundContactBalanceUnit
{
    use UnitConversionByFactorTrait;

    /**
     * @return int|float
     */
    public function getFactorFromBaseUnit()
    {
        return 0.01;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '%L';
    }
}
