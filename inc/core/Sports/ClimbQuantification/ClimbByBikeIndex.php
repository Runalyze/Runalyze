<?php

namespace Runalyze\Sports\ClimbQuantification;

/**
 * @see http://www.climbbybike.com/climb_difficulty.asp
 */
class ClimbByBikeIndex implements ClimbQuantificationInterface
{
    public function getScoreFor($distance, $elevation, $altitudeAtTop = 0)
    {
        return 2 * $elevation / $distance / 10
            + $elevation * $elevation / $distance / 1000
            + $distance
            + max(0, ($altitudeAtTop - 1000) / 100);
    }
}
