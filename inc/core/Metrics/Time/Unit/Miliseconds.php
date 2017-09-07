<?php

namespace Runalyze\Metrics\Time\Unit;

use Runalyze\Metrics\Common\FormattableUnitInterface;
use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class Miliseconds extends AbstractTimeUnit implements FormattableUnitInterface
{
    use UnitConversionByFactorTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'ms';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 1000;
    }

    /**
     * @return int
     */
    public function getDecimals()
    {
        return 0;
    }
}
