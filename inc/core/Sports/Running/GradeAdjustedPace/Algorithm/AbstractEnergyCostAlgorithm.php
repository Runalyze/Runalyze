<?php

namespace Runalyze\Sports\Running\GradeAdjustedPace\Algorithm;

use Runalyze\Sports\Running\GradeAdjustedPace\AlgorithmInterface;

abstract class AbstractEnergyCostAlgorithm implements AlgorithmInterface
{
    /** @var float */
    const TIME_ADJUSTMENT_EXPONENT = 0.83;

    abstract public function getEnergyCost($gradientInPercent);

    public function getTimeFactor($gradientInPercent)
    {
        $energyCost = $this->getEnergyCost($gradientInPercent);

        if ($energyCost <= 0.0) {
            return 0.0;
        }

        return pow(1.0 / $energyCost, self::TIME_ADJUSTMENT_EXPONENT);
    }

    public function adjustPace($paceInSecondsPerKilometer, $gradientInPercent)
    {
        return $paceInSecondsPerKilometer * $this->getTimeFactor($gradientInPercent);
    }
}
