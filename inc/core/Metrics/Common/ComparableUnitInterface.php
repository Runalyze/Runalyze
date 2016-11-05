<?php

namespace Runalyze\Metrics\Common;

interface ComparableUnitInterface extends UnitInterface
{
    /**
     * @param mixed $baseValueInBaseUnit
     * @param mixed $comparisonValueInBaseUnit
     * @return mixed value in base unit
     */
    public function compareBaseUnit($baseValueInBaseUnit, $comparisonValueInBaseUnit);
}
