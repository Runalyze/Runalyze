<?php

namespace Runalyze\Mathematics\Distribution;

/**
 * Empirical distribution for time series
 */
class TimeSeries extends EmpiricalDistribution
{
    /**
     * @param array $data array of data points
     * @param array $time continuous time points
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data, array $time)
    {
        if (empty($time)) {
            throw new \InvalidArgumentException('Time array must not be empty.');
        } elseif (count($time) < count($data)) {
            throw new \InvalidArgumentException('Time array must be at least as large as data array.');
        }

        $histogram = [];
        $lastTime = 0;

        foreach ($data as $i => $val) {
            if (!isset($histogram[$val])) {
                $histogram[$val] = $time[$i] - $lastTime;
            } else {
                $histogram[$val] += $time[$i] - $lastTime;
            }

            $lastTime = $time[$i];
        }

        parent::__construct($histogram, true);
    }
}
