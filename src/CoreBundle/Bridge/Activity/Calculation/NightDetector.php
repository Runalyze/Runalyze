<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use League\Geotools\Geohash\Geohash;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Util\LocalTime;

class NightDetector
{
    /**
     * @param Training $activity
     * @return bool|null
     */
    public function isActivityAtNight(Training $activity)
    {
        if (!$activity->hasRoute() || !$activity->getRoute()->hasGeohashes()) {
            return null;
        }

        // TODO use activity's offset if known
        $detector = new \Runalyze\Calculation\NightDetector();
        $detector->setFrom(
            (new LocalTime($activity->getTime()))->toServerTimestamp() + 0.5 * $activity->getS(),
            (new Geohash())->decode($activity->getRoute()->getStartpoint())->getCoordinate()
        );

        return $detector->isNight();
    }
}
