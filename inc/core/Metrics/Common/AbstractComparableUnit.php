<?php

namespace Runalyze\Metrics\Common;

abstract class AbstractComparableUnit implements ComparableUnitInterface
{
    /**
     * @param mixed $baseValueInBaseUnit
     * @param mixed $comparisonValueInBaseUnit
     * @return mixed value in base unit
     */
    public function compareBaseUnit($baseValueInBaseUnit, $comparisonValueInBaseUnit)
    {
        return $this->toBaseUnit(
            $this->fromBaseUnit($baseValueInBaseUnit) - $this->fromBaseUnit($comparisonValueInBaseUnit)
        );
    }
}
