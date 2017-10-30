<?php

namespace Runalyze\Bundle\CoreBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\RecalculationManager;

class RaceResultListener
{
    /** @var RecalculationManager */
    protected $RecalculationManager;

    public function __construct(RecalculationManager $manager)
    {
        $this->RecalculationManager = $manager;
    }

    public function postPersist(Raceresult $race, LifecycleEventArgs $args)
    {
        $this->scheduleCorrectionFactorRecalculationIfRequired($race);
    }

    public function postUpdate(Raceresult $race, LifecycleEventArgs $args)
    {
        $this->scheduleCorrectionFactorRecalculationIfRequired($race);
    }

    public function postRemove(Raceresult $race, LifecycleEventArgs $args)
    {
        $this->scheduleCorrectionFactorRecalculationIfRequired($race);
    }

    protected function scheduleCorrectionFactorRecalculationIfRequired(Raceresult $race)
    {
        $activity = $race->getActivity();

        if (
            $activity->getSport()->getInternalSport()->isRunning() &&
            $activity->getUseVO2max() &&
            $activity->getVO2max() > 0
        ) {
            $this->RecalculationManager->scheduleEffectiveVO2maxCorrectionFactorCalculation($race->getAccount());
        }
    }
}
