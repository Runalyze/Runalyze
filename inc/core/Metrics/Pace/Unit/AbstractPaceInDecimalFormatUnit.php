<?php

namespace Runalyze\Metrics\Pace\Unit;

use Runalyze\Metrics\Common\FormattableUnitInterface;
use Runalyze\Metrics\Common\UnitConversionByDividendTrait;

abstract class AbstractPaceInDecimalFormatUnit extends AbstractPaceUnit implements FormattableUnitInterface
{
    use UnitConversionByDividendTrait;

    /**
     * @return int
     */
    public function getDecimals()
    {
        return 1;
    }
}
