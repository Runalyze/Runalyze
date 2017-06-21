<?php

namespace Runalyze\Sports\ClimbQuantification;

/**
 * @see https://www.pjammcycling.com/fiets-index.html
 */
class FietsIndex extends AbstractAdditiveClimbQuantification implements CategorizableInterface
{
    public function getScoreFor($distance, $elevation, $altitudeAtTop = 0)
    {
        if ($elevation < 0) {
            return 0.0;
        }

        $scoreWithoutAltitudeAtTop = $elevation * $elevation / ($distance * 10000);
        $scoreForAltitudeAtTop = max(0, ($altitudeAtTop - 1000) / 1000);

        return min($scoreWithoutAltitudeAtTop + $scoreForAltitudeAtTop, 1.5 * $scoreWithoutAltitudeAtTop);
    }

    public function getScoreForProfile(array $distancesAndGradients, $altitudeAtTop = 0)
    {
        $score = parent::getScoreForProfile($distancesAndGradients, 0);
        $scoreForAltitudeAtTop = max(0, ($altitudeAtTop - 1000) / 1000);

        return min($score + $scoreForAltitudeAtTop, 1.5 * $score);
    }

    public function getLowerLimitsForCategorization()
    {
        return [6.5, 5.0, 3.5, 2.0, 0.5, 0.25];
    }
}
