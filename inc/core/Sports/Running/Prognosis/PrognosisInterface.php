<?php

namespace Runalyze\Sports\Running\Prognosis;

interface PrognosisInterface
{
    /**
     * @param float $distance [km]
     * @return int|null prognosis for given distance [s], null if not possible
     */
    public function getSeconds($distance);

    /**
     * @return bool
     */
    public function areValuesValid();
}
