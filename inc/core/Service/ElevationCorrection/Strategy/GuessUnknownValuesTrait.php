<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

trait GuessUnknownValuesTrait
{
    protected $GuessUnknown = true;

    public function setGuessUnknownFlag($flag = true)
    {
        $this->GuessUnknown = $flag;
    }

    /**
     * @param int[]|null $altitudes [m]
     * @param int $unknownValue
     */
    public function guessUnknown(array &$altitudes = null, $unknownValue = -32768)
    {
        if (!$this->GuessUnknown || null === $altitudes) {
            return;
        }

        $numberOfPoints = count($altitudes);
        $i = 0;

        while ($i < $numberOfPoints && $altitudes[$i] === $unknownValue) {
            $i++;
        };

        $lastKnown = $i < $numberOfPoints ? $altitudes[$i] : null;

        for ($i = 0; $i < $numberOfPoints; $i++) {
            if ($altitudes[$i] === $unknownValue) {
                $altitudes[$i] = $lastKnown;
            } else {
                $lastKnown = $altitudes[$i];
            }
        }
    }
}
