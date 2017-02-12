<?php

namespace Runalyze\Sports\Running\Prognosis;

use Runalyze\Calculation\JD\VDOT;
use Runalyze\Mathematics\Numerics\Bisection;

/**
 * Prognosis by Jack Daniels
 *
 * Competition prediction based on "Die Laufformel" by Jack Daniels.
 * See page 52/53 for a table.
 *
 * An adjustment based on a value for the basic endurance can be used.
 * This adjustment is NOT based on Jack Daniels' formulas.
 */
class Daniels implements PrognosisInterface
{
    /** @var float */
    protected $Vdot = 0.0;

    /** @var bool */
    protected $AdjustVdotForBasicEndurance = false;

    /**
     * Basic endurance
     *
     * Basic endurance is interpreted as a percentage of achieved (optimal)
     * marathon training. A value of '100' represents a perfect training.
     * The value can be greater than 100 for representing a good training for
     * an ultramarathon.
     *
     * @var float|int
     */
    protected $BasicEndurance = 0.0;

    /**
     * @param float $vdot
     * @param bool $adjustForBasicEndurance
     * @param float $basicEndurance
     */
    public function __construct($vdot = 0.0, $adjustForBasicEndurance = false, $basicEndurance = 0.0)
    {
        $this->setVdot($vdot);
        $this->adjustForBasicEndurance($adjustForBasicEndurance);
        $this->setBasicEndurance($basicEndurance);
    }

    /**
     * @param float $vdot
     * @return $this
     */
    public function setVdot($vdot)
    {
        $this->Vdot = $vdot;

        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function adjustForBasicEndurance($flag = true)
    {
        $this->AdjustVdotForBasicEndurance = $flag;

        return $this;
    }

    /**
     * @param float $basicEndurance
     * @return $this
     */
    public function setBasicEndurance($basicEndurance)
    {
        $this->BasicEndurance = $basicEndurance;

        return $this;
    }

    public function areValuesValid()
    {
        return VDOT::REASONABLE_MINIMUM <= $this->Vdot && $this->Vdot <= VDOT::REASONABLE_MAXIMUM;
    }

	public function getSeconds($distance)
    {
        return self::getPrognosisInSecondsFor($this->getAdjustedVdotForDistanceIfWanted($distance), $distance);
	}

    /**
     * @param float $distance [km]
     * @param float $vdot
     * @return float|int|null
     */
	public function getSecondsFor($distance, $vdot)
    {
        return $this->setVdot($vdot)->getSeconds($distance);
    }

    /**
     * @param float $vdotToReach
     * @param float $distance [km]
     * @return float|null [s]
     */
    public static function getPrognosisInSecondsFor($vdotToReach, $distance = 5.0)
    {
        if ($vdotToReach < VDOT::REASONABLE_MINIMUM || $vdotToReach > VDOT::REASONABLE_MAXIMUM) {
            return null;
        }

        return (new Bisection($vdotToReach, round(2 * 60 * $distance), round(10 * 60 * $distance),
            function($seconds) use ($distance) {
                return VDOT::formula($distance, $seconds);
            }
        ))->findValue();
    }

    /**
     * @param float $distance [km]
     * @return float (adjusted) vdot
     */
    public function getAdjustedVdotForDistanceIfWanted($distance)
    {
        if ($this->AdjustVdotForBasicEndurance)
            return $this->getAdjustedVDOTforDistance($distance);

        return $this->Vdot;
    }

    /**
     * @param float $distance [km]
     * @return float factor
     */
    public function getAdjustedVdotForDistance($distance)
    {
        return $this->Vdot * $this->getAdjustmentFactor($distance);
    }

    /**
     * Get adjustment factor
     *
     * Get a factor between 0 and 1 (in fact between 0.6 and 1) for adjusting
     * the VDOT to the given distance based on used basic endurance value.
     *
     * Uses <code>pow($distance, 1.23)</code> to predict the required basic endurance.
     *
     * @param float $distance [km]
     * @return float factor
     */
    public function getAdjustmentFactor($distance)
    {
        $requiredBasicEndurance = pow($distance, 1.23);
        $basicEnduranceFactor = max(0.0, 1 - ($requiredBasicEndurance - $this->BasicEndurance) / 100.0);

        return min(1.0, 0.6 + 0.4 * $basicEnduranceFactor);
    }
}
