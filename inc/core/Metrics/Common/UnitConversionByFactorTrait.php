<?php

namespace Runalyze\Metrics\Common;

trait UnitConversionByFactorTrait
{
    /**
     * @return int|float
     */
    abstract public function getFactorFromBaseUnit();

    /**
     * @param mixed $valueInThisUnit
     * @return mixed
     */
    final public function toBaseUnit($valueInThisUnit)
    {
        return $valueInThisUnit / $this->getFactorFromBaseUnit();
    }

    /**
     * @param mixed $valueInBaseUnit
     * @return mixed
     */
    final public function fromBaseUnit($valueInBaseUnit)
    {
        return $valueInBaseUnit * $this->getFactorFromBaseUnit();
    }
}
