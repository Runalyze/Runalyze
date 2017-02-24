<?php

namespace Runalyze\Sports\Running\Prognosis;

use Runalyze\Mathematics\Numerics\Bisection;
use Runalyze\Sports\Running\VO2max\DanielsGilbertFormula;

/**
 * Prognosis based on VO2max [ml/kg/min] (to be correct, there should be a dot above the V)
 *
 * An adjustment based on a value for the athletes basic endurance can be used,
 * as these prognoses are very optimistic for long distances otherwise.
 * This adjustment is based on our own intention only.
 */
class VO2max implements PrognosisInterface
{
    /** @var int Values below are considered invalid */
    const REASONABLE_VO2MAX_MINIMUM = 15;

    /** @var int Values above are considered invalid */
    const REASONABLE_VO2MAX_MAXIMUM = 90;

    /** @var float [ml/kg/min] */
    protected $EffectiveVO2max = 0.0;

    /** @var bool */
    protected $AdjustForMarathonShape = false;

    /**
     * Marathon shape
     *
     * Previously labeled as basic endurance, this value is interpreted as
     * a percentage of achieved (optimal) marathon training.
     * A value of '100' represents a perfect preparation. Values can be greater
     * than 100 for representing a good training for an ultramarathon.
     *
     * @var float|int
     */
    protected $MarathonShapeInPercent = 0.0;

    /**
     * @param float $effectiveVO2max [ml/kg/min]
     * @param bool $adjustForMarathonShape
     * @param float $marathonShapeInPercent
     */
    public function __construct($effectiveVO2max = 0.0, $adjustForMarathonShape = false, $marathonShapeInPercent = 0.0)
    {
        $this->setEffectiveVO2max($effectiveVO2max);
        $this->adjustForMarathonShape($adjustForMarathonShape);
        $this->setMarathonShape($marathonShapeInPercent);
    }

    /**
     * @param float $effectiveVO2max [ml/kg/min]
     * @return $this
     */
    public function setEffectiveVO2max($effectiveVO2max)
    {
        $this->EffectiveVO2max = $effectiveVO2max;

        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function adjustForMarathonShape($flag = true)
    {
        $this->AdjustForMarathonShape = $flag;

        return $this;
    }

    /**
     * @param float $marathonShapeInPercent [%, i.e. 100 = 100%]
     * @return $this
     */
    public function setMarathonShape($marathonShapeInPercent)
    {
        $this->MarathonShapeInPercent = $marathonShapeInPercent;

        return $this;
    }

    public function areValuesValid()
    {
        return self::REASONABLE_VO2MAX_MINIMUM <= $this->EffectiveVO2max && $this->EffectiveVO2max <= self::REASONABLE_VO2MAX_MAXIMUM;
    }

    public function getSeconds($distance)
    {
        return self::getPrognosisInSecondsFor($this->getAdjustedVO2maxForDistanceIfWanted($distance), $distance);
    }

    /**
     * @param float $distance [km]
     * @param float $effectiveVO2max [ml/kg/min]
     * @return float|int|null
     */
    public function getSecondsFor($distance, $effectiveVO2max)
    {
        return $this->setEffectiveVO2max($effectiveVO2max)->getSeconds($distance);
    }

    /**
     * @param float $effectiveVO2maxToReach [ml/kg/min]
     * @param float $distance [km]
     * @return float|null [s]
     */
    public static function getPrognosisInSecondsFor($effectiveVO2maxToReach, $distance = 5.0)
    {
        if ($effectiveVO2maxToReach < self::REASONABLE_VO2MAX_MINIMUM || $effectiveVO2maxToReach > self::REASONABLE_VO2MAX_MAXIMUM) {
            return null;
        }

        return (new Bisection($effectiveVO2maxToReach, round(2 * 60 * $distance), round(10 * 60 * $distance),
            function ($seconds) use ($distance) {
                return DanielsGilbertFormula::evaluate($distance, $seconds);
            }
        ))->findValue();
    }

    /**
     * @param float $distance [km]
     * @return float (adjusted) VO2max [ml/kg/min]
     */
    public function getAdjustedVO2maxForDistanceIfWanted($distance)
    {
        if ($this->AdjustForMarathonShape) {
            return $this->getAdjustedVO2maxForDistance($distance);
        }

        return $this->EffectiveVO2max;
    }

    /**
     * @param float $distance [km]
     * @return float factor
     */
    public function getAdjustedVO2maxForDistance($distance)
    {
        return $this->EffectiveVO2max * $this->getAdjustmentFactor($distance);
    }

    /**
     * Get adjustment factor
     *
     * Get a factor between 0 and 1 (in fact between 0.6 and 1) for adjusting
     * the VO2max to the given distance based on used marathon shape value.
     *
     * Uses <code>pow($distance, 1.23)</code> to predict the required marathon shape.
     *
     * @param float $distance [km]
     * @return float factor [0.0 .. 1.0]
     */
    public function getAdjustmentFactor($distance)
    {
        $requiredMarathonShape = pow($distance, 1.23);
        $marathonShapeFactor = max(0.0, 1 - ($requiredMarathonShape - $this->MarathonShapeInPercent) / 100.0);

        return min(1.0, 0.6 + 0.4 * $marathonShapeFactor);
    }
}
