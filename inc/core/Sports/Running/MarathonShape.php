<?php

namespace Runalyze\Sports\Running;

use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\BasicEndurance;

class MarathonShape
{
    /** @var float [ml/kg/min] */
    const MINIMAL_EFFECTIVE_VO2MAX = 25.0;

    /** @var float [ml/kg/min] */
    protected $EffectiveVO2max;

    /** @var BasicEndurance */
    protected $Configuration;

    /**
     * @param float [ml/kg/min] $effectiveVO2max
     * @param BasicEndurance $configuration
     */
    public function __construct($effectiveVO2max, BasicEndurance $configuration)
    {
        $this->EffectiveVO2max = $effectiveVO2max;
        $this->Configuration = $configuration;
    }

    /**
     * @param float [ml/kg/min] $effectiveVO2max
     * @return $this
     */
    public function setEffectiveVO2max($effectiveVO2max)
    {
        $this->EffectiveVO2max = $effectiveVO2max;

        return $this;
    }

    /**
     * @param float $totalDistanceInTimePeriod [km]
     * @param float $totalWeightedRelativeLongJogPoints one point per week matches the target
     * @param int|null $numberOfDaysSinceFirstActivity
     * @return int [0, inf)
     */
    public function getShapeFor($totalDistanceInTimePeriod, $totalWeightedRelativeLongJogPoints, $numberOfDaysSinceFirstActivity = null)
    {
        $percentageWeekly = $totalDistanceInTimePeriod * 7.0 / $this->Configuration->getDaysToConsiderForWeeklyMileage($numberOfDaysSinceFirstActivity) / $this->getTargetForWeeklyMileage();
        $percentageLongJogs = $totalWeightedRelativeLongJogPoints * 7.0 / $this->Configuration->getDaysToConsiderForLongJogs();

        return (int)round(100.0 * (
            $percentageWeekly * $this->Configuration->getPercentageForWeeklyMileage() +
            $percentageLongJogs * $this->Configuration->getPercentageForLongJogs()
        ));
    }

    /**
     * @return float [km]
     */
    public function getTargetForWeeklyMileage()
    {
        return pow(max($this->EffectiveVO2max, self::MINIMAL_EFFECTIVE_VO2MAX), 1.135);
    }

    /**
     * @return float [km]
     */
    public function getTargetForLongJogEachWeek()
    {
        return log(max($this->EffectiveVO2max, self::MINIMAL_EFFECTIVE_VO2MAX) / 4.0) * 12.0;
    }
}
