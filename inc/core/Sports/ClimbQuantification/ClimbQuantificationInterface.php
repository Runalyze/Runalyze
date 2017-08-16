<?php

namespace Runalyze\Sports\ClimbQuantification;

interface ClimbQuantificationInterface
{
    /**
     * @param float $distance [km]
     * @param int $elevation [m]
     * @param int $altitudeAtTop [m]
     * @return mixed
     */
    public function getScoreFor($distance, $elevation, $altitudeAtTop = 0);
}
