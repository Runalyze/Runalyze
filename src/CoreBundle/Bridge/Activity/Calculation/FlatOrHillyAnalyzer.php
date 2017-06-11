<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Training;

class FlatOrHillyAnalyzer
{
    public function calculatePercentageFlatFor(Training $activity, $threshold = 0.02)
    {
        $percentageFlat = 0.0;

        // TODO

        // $activity->setPercentageFlat($percentageFlat);

        return $percentageFlat;
    }
}
