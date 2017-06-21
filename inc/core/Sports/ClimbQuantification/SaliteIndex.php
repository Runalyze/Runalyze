<?php

namespace Runalyze\Sports\ClimbQuantification;

/**
 * @see http://www.salite.ch/struttura/indice_diff.asp
 */
class SaliteIndex extends AbstractAdditiveClimbQuantification
{
    public function getScoreFor($distance, $elevation, $altitudeAtTop = 0)
    {
        return $elevation * $elevation / $distance / 100;
    }
}
