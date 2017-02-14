<?php

namespace Runalyze\Sports\Running\Prognosis;

/**
 * Prognosis by Herbert Steffny
 *
 * Competition prediction based on "Das große Laufbuch" by Herbert Steffny.
 * See page 136.
 *
 * A linear approximation (based on the pace) is used for distances between the given distances in the book,
 * e.g. to predict a 7.5k-race from 5k in 20:00 (4:00/km), look at 10k performance (41:00, 4:06/km) and
 * predict 4:03/km and therefore 30:23.
 */
class Steffny implements PrognosisInterface
{
    /** @var float [s/km] */
    protected $ReferencePaceFor10k = 0.0;

    /**
     * @param float|int $referenceTime [s]
     * @param float $referenceDistance [km]
     */
    public function __construct($referenceTime = 0.0, $referenceDistance = 10.0)
    {
        $this->setReferenceResult($referenceDistance, $referenceTime);
    }

    public function areValuesValid()
    {
        return 150 < $this->ReferencePaceFor10k;
    }

    /**
     * @param int|float $timeInSeconds [s]
     * @return $this
     */
    public function setReferenceFrom10kTime($timeInSeconds)
    {
        $this->ReferencePaceFor10k = (float)$timeInSeconds / 10.0;

        return $this;
    }

    /**
     * @param float $distance [km]
     * @param int|float $timeInSeconds [s]
     * @return $this
     */
    public function setReferenceResult($distance, $timeInSeconds)
    {
        $this->transformToNear10k($distance, $timeInSeconds);

        $this->ReferencePaceFor10k = $timeInSeconds / $distance;

        return $this;
    }

    /**
     * @param float $distance [km]
     * @param float $timeInSeconds [s]
     */
    protected function transformToNear10k(&$distance, &$timeInSeconds)
    {
        if ($distance < 10.0) {
            $this->transformFromBelowToNear10k($distance, $timeInSeconds);
        } else {
            $this->transformFromAboveToNear10k($distance, $timeInSeconds);
        }
    }

    /**
     * @param float $distance [km]
     * @param float $timeInSeconds [s]
     */
    protected function transformFromBelowToNear10k(&$distance, &$timeInSeconds)
    {
        if ($distance <= 2.25) {
            $timeInSeconds = $this->from1500mTo3000m(1.5 * $timeInSeconds / $distance);
            $distance = 3.0;
        }

        if ($distance <= 4.0) {
            $timeInSeconds = $this->from3000mTo5k(3.0 * $timeInSeconds / $distance);
            $distance = 5.0;
        }

        if ($distance <= 7.5) {
            $timeInSeconds = $this->from5kTo10k(5.0 * $timeInSeconds / $distance);
            $distance = 10.0;
        }
    }

    /**
     * @param float $distance [km]
     * @param float $timeInSeconds [s]
     */
    protected function transformFromAboveToNear10k(&$distance, &$timeInSeconds)
    {
        if ($distance >= 31.6) {
            $timeInSeconds = $this->fromHalfMarathonToMarathon(42.195 * $timeInSeconds / $distance, true);
            $distance = 21.0975;
        }

        if ($distance >= 15.55) {
            $timeInSeconds = $this->from10kToHalfMaraton(21.0975 * $timeInSeconds / $distance, true);
            $distance = 10.0;
        }
    }

    public function getSeconds($distance)
    {
        $paces = [];
        $paces['10k'] = $this->ReferencePaceFor10k;
        $paces['5k'] = $this->from5kTo10k(10.0 * $paces['10k'], true) / 5.0;
        $paces['3000m'] = $this->from3000mTo5k(5.0 * $paces['5k'], true) / 3.0;
        $paces['1500m'] = $this->from1500mTo3000m(3.0 * $paces['3000m'], true) / 1.5;
        $paces['HM'] = $this->from10kToHalfMaraton(10.0 * $paces['10k']) / 21.0975;
        $paces['M'] = $this->fromHalfMarathonToMarathon(21.0975 * $paces['HM']) / 42.195;
        $paces['100k'] = $this->fromMarathonTo100k(42.195 * $paces['M']) / 100.0;

        if ($distance <= 1.5) {
            return $distance * $paces['1500m'];
        } elseif ($distance <= 3.0) {
            return $distance * ($paces['1500m'] + ($paces['3000m'] - $paces['1500m']) * ($distance - 1.5) / (3 - 1.5));
        } elseif ($distance <= 5.0) {
            return $distance * ($paces['3000m'] + ($paces['5k'] - $paces['3000m']) * ($distance - 3.0) / (5.0 - 3.0));
        } elseif ($distance <= 10.0) {
            return $distance * ($paces['5k'] + ($paces['10k'] - $paces['5k']) * ($distance - 5.0) / (10.0 - 5.0));
        } elseif ($distance <= 21.0975) {
            return $distance * ($paces['10k'] + ($paces['HM'] - $paces['10k']) * ($distance - 10.0) / (21.0975 - 10.0));
        } elseif ($distance <= 42.195) {
            return $distance * ($paces['HM'] + ($paces['M'] - $paces['HM']) * ($distance - 21.0975) / (42.195 - 21.0975));
        } elseif ($distance <= 100.0) {
            return $distance * ($paces['M'] + ($paces['100k'] - $paces['M']) * ($distance - 42.195) / (100.0 - 42.195));
        }

        return $distance * $paces['100k'];
    }

    /**
     * @param float $timeInSeconds [s]
     * @param bool $calculateBackwards
     * @return float [s]
     */
    protected function from1500mTo3000m($timeInSeconds, $calculateBackwards = false)
    {
        if ($calculateBackwards) {
            return ($timeInSeconds - 20.0) / 2.0;
        }

        return $timeInSeconds * 2.0 + 20.0;
    }

    /**
     * @param float $timeInSeconds [s]
     * @param bool $calculateBackwards
     * @return float [s]
     */
    protected function from3000mTo5k($timeInSeconds, $calculateBackwards = false)
    {
        if ($calculateBackwards) {
            return ($timeInSeconds / 1.666) - 20.0;
        }

        return ($timeInSeconds + 20.0) * 1.666;
    }

    /**
     * @param float $timeInSeconds [s]
     * @param bool $calculateBackwards
     * @return float [s]
     */
    protected function from5kTo10k($timeInSeconds, $calculateBackwards = false)
    {
        if ($calculateBackwards) {
            return ($timeInSeconds - 60) / 2;
        }

        return $timeInSeconds * 2 + 60;
    }

    /**
     * @param float $timeInSeconds [s]
     * @param bool $calculateBackwards
     * @return float [s]
     */
    protected function from10kToHalfMaraton($timeInSeconds, $calculateBackwards = false)
    {
        if ($calculateBackwards) {
            return $timeInSeconds / 2.21;
        }

        return $timeInSeconds * 2.21;
    }

    /**
     * @param float $timeInSeconds [s]
     * @param bool $calculateBackwards
     * @return float
     */
    protected function fromHalfMarathonToMarathon($timeInSeconds, $calculateBackwards = false)
    {
        if ($calculateBackwards) {
            return $timeInSeconds / 2.11;
        }

        return $timeInSeconds * 2.11;
    }

    /**
     * @param float $timeInSeconds [s]
     * @return float [s]
     */
    protected function fromMarathonTo100k($timeInSeconds)
    {
        return $timeInSeconds * 3.0 - max(0.0, 3.0 * 3600.0 - $timeInSeconds);
    }
}
