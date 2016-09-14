<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Table;

use Runalyze\Activity\Duration;
use Runalyze\Calculation\Prognosis;

class VdotRaceResultsTable
{
    /**
     * @param float[] $distances
     * @param int[] $vdotValues
     * @return array ['vdot' => ['value' => 'vdot', 'results' => [prognoses for $distances]], ...]
     */
    public function getVdotRaceResults(array $distances, array $vdotValues)
    {
        $Strategy = new Prognosis\Daniels();
        $Strategy->adjustVDOT(false);

        $Prognosis = new Prognosis\Prognosis;
        $Prognosis->setStrategy($Strategy);

        $vdots = [];

        foreach ($vdotValues as $vdot) {
            $Strategy->setVDOT($vdot);

            $vdots[$vdot] = [
                'value' => $vdot,
                'results' => []
            ];

            foreach ($distances as $km) {
                $vdots[$vdot]['results'][] = Duration::format(round($Prognosis->inSeconds($km)));
            }
        }

        return $vdots;
    }
}
