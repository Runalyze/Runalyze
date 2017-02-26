<?php

namespace Runalyze\Calculation\JD;

use Runalyze\Sports\Running\VO2max\Estimation\DanielsGilbertFormula;
use Runalyze\Sports\Running\VO2max\VO2maxVelocity;

/**
 * @deprecated since v3.3|4.0
 */
class LegacyEffectiveVO2max
{
    /** @var int */
    private static $Precision = 2;

    /** @var float [ml/kg/min] */
    protected $Value;

    /** @var \Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector */
    protected $Corrector;

    /**
     * @param int $decimals number of decimals to display
     */
    public static function setPrecision($decimals)
    {
        self::$Precision = $decimals;
    }

    /**
     * @param float $value [optional]
     * @param \Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector $corrector [optional]
     */
    public function __construct($value = 0.0, LegacyEffectiveVO2maxCorrector $corrector = null)
    {
        $this->setValue($value);
        $this->setCorrector($corrector);
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->Value = $value;
    }

    /**
     * @param \Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector $corrector [optional]
     */
    public function setCorrector(LegacyEffectiveVO2maxCorrector $corrector = null)
    {
        if (!is_null($corrector)) {
            $this->Corrector = $corrector;
        }
    }

    /**
     * @param float $distance [km]
     * @param int $seconds [s]
     */
    public function fromPace($distance, $seconds)
    {
        $this->Value = (new DanielsGilbertFormula())->estimateFromRaceResult($distance, $seconds);
    }

    /**
     * @param float $distance [km]
     * @param int $seconds [s]
     * @param float $hrInPercent in [0.0, 1.0]
     */
    public function fromPaceAndHR($distance, $seconds, $hrInPercent)
    {
        if ($hrInPercent <= 0.0 || $seconds == 0) {
            $this->Value = 0.0;
        } else {
            $speedReallyAchieved = 60 * 1000 * $distance / $seconds;
            $percentageEstimateByHR = self::percentageAt($hrInPercent);
            $speedEstimateAt100Percent = $speedReallyAchieved / $percentageEstimateByHR;

            $this->fromSpeed($speedEstimateAt100Percent);
        }
    }

    /**
     * @return float [ml/kg/min]
     */
    public function value()
    {
        return number_format($this->exactValue(), self::$Precision);
    }

    /**
     * @codeCoverageIgnore
     * @return float [ml/kg/min]
     */
    public function exactValue()
    {
        if (!is_null($this->Corrector)) {
            return $this->Corrector->factor() * $this->Value;
        }

        return $this->Value;
    }

    /**
     * @return float [ml/kg/min]
     */
    public function uncorrectedValue()
    {
        return number_format($this->Value, self::$Precision);
    }

    /**
     * @param float $speed [m/min] speed for 100%vVO2max
     */
    public function fromSpeed($speed)
    {
        $this->Value = (new DanielsGilbertFormula())->estimateFromVelocity($speed);
    }

    /**
     * Speed at 100%vVO2max
     *
     * @return float [m/min]
     */
    public function speed()
    {
        return (new VO2maxVelocity())->getVelocity($this->Value);
    }

    /**
     * Pace at 100%
     *
     * @return int [s/km]
     */
    public function pace()
    {
        if ($this->Value <= 0.0) {
            return 0;
        }

        return round(60 * 1000 / $this->speed());
    }

    /**
     * Pace at %vVO2max
     *
     * @param float $percentage in (0.0, 1.0]
     * @return int
     */
    public function paceAt($percentage)
    {
        if ($this->Value <= 0.0) {
            return 0;
        }

        return round(60 * 1000 / ($percentage * $this->speed()));
    }

    /**
     * Expected heart rate at X.X % of VO2max
     *
     * This formula is derived via regression from respective tables.
     *
     * @param float $percentage in [0.0, 1.0]
     * @return float in [0.0, 1.0]
     */
    public static function HRat($percentage)
    {
        return 0.68725 * log($percentage) + 1.00466;
    }

    /**
     * Expected % of VO2max at given heart rate
     *
     * This formula is derived via regression from respective tables.
     *
     * @param float $hrInPercent in [0.0, 1.0]
     * @return float in [0.0, 1.0]
     */
    public static function percentageAt($hrInPercent)
    {
        return exp(($hrInPercent - 1.00466) / 0.68725);
    }
}
