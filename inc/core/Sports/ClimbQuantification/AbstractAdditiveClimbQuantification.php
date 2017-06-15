<?php

namespace Runalyze\Sports\ClimbQuantification;

abstract class AbstractAdditiveClimbQuantification implements AdditiveClimbQuantificationInterface
{
    /** @var float */
    protected $MaximalGradient = 0.25;

    public function getScoreForProfile(array $distancesAndGradients, $altitudeAtTop = 0)
    {
        $numSegments = count($distancesAndGradients);
        $score = 0.0;

        for ($i = 0; $i < $numSegments; ++$i) {
            $score += $this->getScoreFor(
                $distancesAndGradients[$i][0],
                $distancesAndGradients[$i][0] * min($this->MaximalGradient, $distancesAndGradients[$i][1]) * 1000,
                ($numSegments - 1 == $i) ? $altitudeAtTop : 0
            );
        }

        return $score;
    }

    /**
     * @param float $gradient
     */
    public function setMaximalGradient($gradient)
    {
        $this->MaximalGradient = $gradient;
    }

    /**
     * @return float
     */
    public function getMaximalGradient()
    {
        return $this->MaximalGradient;
    }
}
