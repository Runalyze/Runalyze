<?php

namespace Runalyze\Metrics\Energy;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Energy\Unit\AbstractEnergyUnit;
use Runalyze\Metrics\Energy\Unit\Kilocalories;

class Energy extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return Kilocalories::class;
    }

    /**
     * @param mixed $value
     * @param AbstractEnergyUnit|null $unit
     * @return $this
     */
    public function setValue($value, AbstractEnergyUnit $unit = null)
    {
        $this->Value = null !== $unit ? $unit->toBaseUnit($value) : $value;

        return $this;
    }

    /**
     * @param int|float $met metabolic equivalent [kcal/kg/h]
     * @param int|float $bodyMassInKilogram [kg]
     * @param int|float $timeInHours [h]
     * @return $this
     */
    public function setByMetabolicEquivalent($met, $bodyMassInKilogram, $timeInHours)
    {
        $this->Value = $met * $bodyMassInKilogram * $timeInHours;

        return $this;
    }
}
