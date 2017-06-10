<?php

namespace Runalyze\Sports\ClimbQuantification;

/**
 * @see https://www.pjammcycling.com/fiets-index.html
 */
class FietsIndex extends AbstractAdditiveClimbQuantification implements CategorizableInterface
{
    public function getScoreFor($distance, $elevation, $altitudeAtTop = 0)
    {
        return $elevation * $elevation / ($distance * 10000) + max(0, ($altitudeAtTop - 1000) / 1000);
    }

    public function getLowerLimitsForCategorization()
    {
        return [6.5, 5.0, 3.5, 2.0, 0.5, 0.25];
    }
}
