<?php

namespace Runalyze\Metrics\Common;

trait UnitConversionByDividendTrait
{
    /**
     * @return int|float
     */
    abstract public function getDividendFromBaseUnit();

    /**
     * @param mixed $valueInThisUnit
     * @return mixed
     */
    final public function toBaseUnit($valueInThisUnit)
    {
        return $this->getDividendFromBaseUnit() / $valueInThisUnit;
    }

    /**
     * @param mixed $valueInBaseUnit
     * @return mixed
     */
    final public function fromBaseUnit($valueInBaseUnit)
    {
        return $this->getDividendFromBaseUnit() / $valueInBaseUnit;
    }
}
