<?php

namespace Runalyze\Metrics\Common;

trait BaseUnitTrait
{
    /**
     * @param mixed $valueInThisUnit
     * @return mixed
     */
    public function toBaseUnit($valueInThisUnit)
    {
        return $valueInThisUnit;
    }

    /**
     * @param mixed $valueInBaseUnit
     * @return mixed
     */
    public function fromBaseUnit($valueInBaseUnit)
    {
        return $valueInBaseUnit;
    }
}
