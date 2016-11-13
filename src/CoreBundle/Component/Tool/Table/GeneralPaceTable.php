<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Table;

use Runalyze\Activity\Duration;

class GeneralPaceTable
{
    /**
     * @param float[] $distances
     * @param float[] $secondsRangePer400m
     * @return array [['pace', ...], ...]
     */
    public function getPaces(array $distances, array $secondsRangePer400m)
    {
        $duration = new Duration();
        $rows = [];

        foreach ($secondsRangePer400m as $i => $secondsPer400m) {
            $rows[$i] = [];

            foreach ($distances as $km) {
                $duration->fromSeconds($km * $secondsPer400m/0.4);

                $rows[$i][] = $duration->string('auto', $km >= 0.4 ? 0 : 1);
            }
        }

        return $rows;
    }
}
