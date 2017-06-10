<?php

namespace Runalyze\Sports\ClimbQuantification;

/**
 * @see https://www.pjammcycling.com/fiets-index.html
 */
class FietsIndex extends AbstractAdditiveClimbQuantification
{
    public function getScoreFor($distance, $elevation, $altitudeAtTop = 0)
    {
        return $elevation * $elevation / ($distance * 10000) + max(0, ($altitudeAtTop - 1000) / 1000);
    }
}
