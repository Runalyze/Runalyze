<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Calculation\Activity\PaceSmoother;
use Runalyze\Model\Trackdata\Entity;

class PaceCalculator
{
    public function calculateFor(Trackdata $trackData)
    {
        $pace = null;

        if ($trackData->hasTime() && $trackData->hasDistance()) {
            $legacyTrackData = new Entity([
                Entity::TIME => $trackData->getTime(),
                Entity::DISTANCE => $trackData->getDistance()
            ]);

            $smoother = new PaceSmoother($legacyTrackData, true);
            $pace = $smoother->smooth(0.001, PaceSmoother::MODE_DISTANCE);
        }

        $trackData->setPace($pace);
    }
}
