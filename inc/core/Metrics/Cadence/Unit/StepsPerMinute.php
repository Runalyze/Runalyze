<?php

namespace Runalyze\Metrics\Cadence\Unit;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class StepsPerMinute extends AbstractCadenceUnit
{
    use UnitConversionByFactorTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'spm';
    }

    /**
     * @return int
     */
    public function getFactorFromBaseUnit()
    {
        return 2;
    }
}
