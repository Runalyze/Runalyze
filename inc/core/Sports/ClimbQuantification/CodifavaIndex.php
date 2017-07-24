<?php

namespace Runalyze\Sports\ClimbQuantification;

/**
 * @see http://digilander.libero.it/napobike/Codifava.htm
 * @see http://www.salite.ch/indice_diff_en.asp
 */
class CodifavaIndex implements AdditiveClimbQuantificationInterface
{
    public function getScoreFor($distance, $elevation, $altitudeAtTop = 0)
    {
        $gradientInPercent = $elevation / $distance / 10;

        return $gradientInPercent * $gradientInPercent * $distance / 10 + 4 * $gradientInPercent;
    }

    public function getScoreForProfile(array $distancesAndGradients, $altitudeAtTop = 0)
    {
        $totalElevation = 0;
        $sum = 0;

        foreach ($distancesAndGradients as $segment) {
            $totalElevation += $segment[0] * $segment[1] * 1000;

            $sum += $segment[0] * $segment[1] * $segment[1] * 10000;
        }

        return ($totalElevation + 400) / 10 / $totalElevation * $sum;
    }
}
