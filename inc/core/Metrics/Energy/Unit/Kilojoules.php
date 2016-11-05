<?php

namespace Runalyze\Metrics\Energy\Unit;

use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class Kilojoules extends AbstractEnergyUnit
{
    use UnitConversionByFactorTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'kJ';
    }

    /**
     * @return float
     */
    public function getFactorFromBaseUnit()
    {
        return 4.1868;
    }
}
