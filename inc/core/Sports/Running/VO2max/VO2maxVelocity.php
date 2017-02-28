<?php

namespace Runalyze\Sports\Running\VO2max;

class VO2maxVelocity
{
    /** @var float [ml/kg/min] */
    protected $EffectiveVO2max = 0.0;

    /**
     * @param float|null $effectiveVO2max [ml/kg/min]
     */
    public function __construct($effectiveVO2max = null)
    {
        if (null !== $effectiveVO2max) {
            $this->setEffectiveVO2max($effectiveVO2max);
        }
    }

    /**
     * @param float $effectiveVO2max [ml/kg/min]
     *
     * @return $this
     */
    public function setEffectiveVO2max($effectiveVO2max)
    {
        $this->EffectiveVO2max = $effectiveVO2max;

        return $this;
    }

    /**
     * Velocity calculated as inverse of DanielsGilbertFormula::estimateFromVelocity()
     *
     * @param float|null $effectiveVO2max [ml/kg/min]
     * @param float $percentage %vVO2max, typically in [0.5, 1.2]
     *
     * @return float [m/min]
     */
    public function getVelocity($effectiveVO2max = null, $percentage = 1.0)
    {
        $effectiveVO2max = $effectiveVO2max ?: $this->EffectiveVO2max;
        $effectiveVO2max *= $percentage;

        if ($effectiveVO2max <= 0.0) {
            return 0.0;
        }

        return -876.0 + pow(876.0 * 876.0 + (4.6 + $effectiveVO2max) / 0.000104, 0.5);
    }

    /**
     * @param float $percentage %vVO2max, typically in [0.5, 1.2]
     *
     * @return float [m/min]
     */
    public function getVelocityAt($percentage)
    {
        return $this->getVelocity(null, $percentage);
    }

    /**
     * @param float|null $effectiveVO2max [ml/kg/min]
     * @param float $percentage %vVO2max, typically in [0.5, 1.2]
     *
     * @return float [s/km]
     */
    public function getPace($effectiveVO2max = null, $percentage = 1.0)
    {
        $velocity = $this->getVelocity($effectiveVO2max, $percentage);

        if ($velocity <= 0.0) {
            return 0;
        }

        return (int)round(60000 / $velocity);
    }

    /**
     * @param float $percentage %vVO2max, typically in [0.5, 1.2]
     *
     * @return float [s/km]
     */
    public function getPaceAt($percentage)
    {
        return $this->getPace(null, $percentage);
    }
}
