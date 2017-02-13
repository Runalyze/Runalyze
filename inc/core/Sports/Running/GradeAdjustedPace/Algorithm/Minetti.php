<?php

namespace Runalyze\Sports\Running\GradeAdjustedPace\Algorithm;

/**
 * Grade adjusted pace based on a study by Alberto E. Minetti on the energy cost of
 * walking and running at extreme slopes.
 *
 * @see Minetti, A. E. et al. (2002). Energy cost of walking and running at extreme uphill and downhill slopes.
 *      Journal of Applied Physiology 93, 1039-1046, http://jap.physiology.org/content/93/3/1039.full
 */
class Minetti extends AbstractEnergyCostAlgorithm
{
    public function getEnergyCost($g)
    {
        return 1.0 + ($g * (19.5 + $g * (46.3 + $g * (-43.3 + $g * (-30.4 + $g * 155.4))))) / 3.6;
    }
}
