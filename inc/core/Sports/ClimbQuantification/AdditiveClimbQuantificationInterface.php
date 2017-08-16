<?php

namespace Runalyze\Sports\ClimbQuantification;

interface AdditiveClimbQuantificationInterface extends ClimbQuantificationInterface
{
    /**
     * @param array $distancesAndGradients [[km, gradient in 0.00 .. 1.00], ...]
     * @param int $altitudeAtTop [m]
     * @return mixed
     */
    public function getScoreForProfile(array $distancesAndGradients, $altitudeAtTop = 0);
}
