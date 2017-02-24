<?php

namespace Runalyze\Sports\Running\VO2max;

/**
 * @see J. Daniels, The Conditioning for Distance Running--the Scientific Aspects, John Wiley & Sons, New York, 1978
 * @see T. Noakes, Lore of Running (4th edition), Oxford University Press Southern Africa, 2001
 * @see http://www.simpsonassociatesinc.com/runningmath3.htm
 * @see http://www.had2know.com/health/vo2-max-calculator-racing-daniels-gilbert.html
 */
class DanielsGilbertFormula
{
    /**
     * Calculate VO2max based on race result
     *
     * It can be read as 'oxygen cost' divided by 'drop dead'.
     *
     * @param float $distance [km]
     * @param int $seconds [s]
     * @return float [ml/kg/min]
     */
    public static function evaluate($distance, $seconds) {
        $min = $seconds / 60;
        $m = 1000 * $distance;

        if ($m <= 0.0 || $min <= 0.0 || $m / $min < 50.0 || $m / $min > 1000.0) {
            return 0.0;
        }

        return (-4.6 + 0.182258 * $m / $min + 0.000104 * pow($m / $min, 2))
            / (0.8 + 0.1894393 * exp(-0.012778 * $min) + 0.2989558 * exp(-0.1932605 * $min));
    }
}
