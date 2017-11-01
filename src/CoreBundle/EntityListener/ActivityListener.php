<?php

namespace Runalyze\Bundle\CoreBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\PowerCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\RecalculationManager;
use Runalyze\Util\LocalTime;

class ActivityListener
{
    /** @var RecalculationManager */
    protected $RecalculationManager;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    /** @var SportRepository */
    protected $SportRepository;

    public function __construct(
        RecalculationManager $recalculationManager,
        ConfigurationManager $configurationManager,
        SportRepository $sportRepository
    )
    {
        $this->RecalculationManager = $recalculationManager;
        $this->ConfigurationManager = $configurationManager;
        $this->SportRepository = $sportRepository;
    }

    /**
     * @param Account $account
     * @return int
     */
    protected function getNumberOfDaysToConsiderForEffectiveVO2maxShape(Account $account)
    {
        return $this->ConfigurationManager->getList($account)->getVO2max()->getNumberOfDaysToConsider();
    }

    /**
     * @param Account $account
     * @return int
     */
    protected function getNumberOfDaysToConsiderForMarathonShape(Account $account)
    {
        return $this->ConfigurationManager->getList($account)->getBasicEndurance()->getDaysToConsider();
    }

    public function prePersist(Training $activity, LifecycleEventArgs $args)
    {
        $this->checkRelatedEntitiesForConsistency($activity);
        $this->removeWeatherIfInside($activity);
        $this->calculateEnergyConsumptionIfEmpty($activity);
        $this->calculateTrimp($activity);
        $this->calculatePower($activity);
        $this->calculateIfActivityWasAtNight($activity);
        $this->calculateClimbScore($activity);
        $this->calculateValuesForSwimming($activity);
    }

    public function postPersist(Training $activity, LifecycleEventArgs $args)
    {
        $this->scheduleRunningRelatedRecalculationsIfRequired($activity);
        $this->addStartTimeCheck($activity->getAccount(), $activity->getTime(), false);
    }

    public function preUpdate(Training $activity, PreUpdateEventArgs $args)
    {
        $this->checkRelatedEntitiesForConsistency($activity, $args);

        if ($args->hasChangedField('sport')) {
            $this->removeWeatherIfInside($activity);
            $this->calculatePower($activity);
        }

        if ($args->hasChangedField('time') || $args->hasChangedField('s')) {
            $this->calculateIfActivityWasAtNight($activity);
        }

        $this->scheduleRunningRelatedRecalculationsIfRequiredForUpdate($activity, $args);

        if ($args->hasChangedField('time')) {
            if ($args->getOldValue('time') < $args->getNewValue('time')) {
                $this->addStartTimeCheck($activity->getAccount(), $args->getOldValue('time'), true);
            } else {
                $this->addStartTimeCheck($activity->getAccount(), $args->getNewValue('time'), false);
            }
        }
    }

    public function postRemove(Training $activity, LifecycleEventArgs $args)
    {
        $this->scheduleRunningRelatedRecalculationsIfRequired($activity);

        $this->addStartTimeCheck($activity->getAccount(), $activity->getTime(), true);
    }

    protected function checkRelatedEntitiesForConsistency(Training $activity, PreUpdateEventArgs $args = null)
    {
        if (null === $args || $args->hasChangedField('sport')) {
            $this->setSportIfEmpty($activity);
        }

        if (null === $args || $args->hasChangedField('sport') || $args->hasChangedField('type')) {
            $this->removeTypeIfInvalidForSport($activity);
        }
    }

    protected function setSportIfEmpty(Training $activity)
    {
        if (null === $activity->getSport()) {
            /** @var Sport $mainSport */
            $mainSport = $this->SportRepository->find(
                $this->ConfigurationManager->getList($activity->getAccount())->getGeneral()->getMainSport()
            );
            $activity->setSport($mainSport);
        }
    }

    protected function removeTypeIfInvalidForSport(Training $activity)
    {
        if (null !== $activity->getType() && $activity->getType()->getSport()->getId() != $activity->getSport()->getId()) {
            $activity->setType(null);
        }
    }

    public function removeWeatherIfInside(Training $activity)
    {
        if (!$activity->getSport()->getOutside()) {
            $activity->getAdapter()->removeWeather();
        }
    }

    protected function calculateEnergyConsumptionIfEmpty(Training $activity)
    {
        $activity->getAdapter()->calculateEnergyConsumptionIfEmpty();
    }

    protected function calculateTrimp(Training $activity)
    {
        $dataConfiguration = $this->ConfigurationManager->getList($activity->getAccount())->getData();

        $activity->getAdapter()->calculateTrimp(
            $activity->getAccount()->getGender(),
            $dataConfiguration->getMaximalHeartRate(),
            $dataConfiguration->getRestingHeartRate()
        );
    }

    protected function calculatePower(Training $activity)
    {
        $activity->getAdapter()->calculatePower();
    }

    protected function calculateIfActivityWasAtNight(Training $activity)
    {
        $activity->getAdapter()->calculateIfActivityWasAtNight();
    }

    protected function calculateClimbScore(Training $activity)
    {
        $activity->getAdapter()->calculateClimbScore();
    }

    protected function calculateValuesForSwimming(Training $activity)
    {
        $activity->getAdapter()->calculateValuesForSwimming();
    }

    /**
     * @param Account $account
     * @param int $timestamp
     * @param bool $isRemoved
     */
    protected function addStartTimeCheck(Account $account, $timestamp, $isRemoved)
    {
        $this->RecalculationManager->addStartTimeCheck($account, $timestamp, $isRemoved);
    }

    protected function scheduleRunningRelatedRecalculationsIfRequired(Training $activity)
    {
        if ($activity->getAdapter()->isRunning()) {
            if ($activity->getAdapter()->isRelevantForCurrentEffectiveVO2maxShape($this->getNumberOfDaysToConsiderForEffectiveVO2maxShape($activity->getAccount()))) {
                $this->RecalculationManager->scheduleEffectiveVO2maxShapeCalculation($activity->getAccount());
            }

            if ($activity->getAdapter()->isRelevantForCurrentMarathonShape($this->getNumberOfDaysToConsiderForMarathonShape($activity->getAccount()))) {
                $this->RecalculationManager->scheduleMarathonShapeCalculation($activity->getAccount());
            }
        }
    }

    protected function scheduleRunningRelatedRecalculationsIfRequiredForUpdate(Training $activity, PreUpdateEventArgs $args)
    {
        if ($this->updateRequiresEffectiveVO2maxShapeCalculation($activity, $args)) {
            $this->RecalculationManager->scheduleEffectiveVO2maxShapeCalculation($activity->getAccount());
        }

        if ($this->updateRequiresMarathonShapeCalculation($activity, $args)) {
            $this->RecalculationManager->scheduleMarathonShapeCalculation($activity->getAccount());
        }
    }

    protected function updateRequiresEffectiveVO2maxShapeCalculation(Training $activity, PreUpdateEventArgs $args)
    {
        $days = $this->getNumberOfDaysToConsiderForEffectiveVO2maxShape($activity->getAccount());

        return ((
            (
                $args->hasChangedField('useVO2max') ||
                $args->hasChangedField('vo2max') ||
                $args->hasChangedField('vo2maxWithElevation')
            ) && (
                $activity->getAdapter()->isNotOlderThanXDays($days) ||
                $this->timestampEnteredOrLeftPeriodOfLastXDays($days, $args)
            )
        ) || (
            $this->timestampEnteredOrLeftPeriodOfLastXDays($days, $args) && (
                $activity->getUseVO2max() &&
                $activity->getVO2max() > 0.0
            )
        ));
    }

    /**
     * @param Training $activity
     * @param PreUpdateEventArgs $args
     * @return bool
     */
    protected function updateRequiresMarathonShapeCalculation(Training $activity, PreUpdateEventArgs $args)
    {
        return (
            $activity->getAdapter()->isRunning() &&
            $this->timestampEnteredOrLeftPeriodOfLastXDays($this->getNumberOfDaysToConsiderForMarathonShape($activity->getAccount()), $args)
        );
    }

    /**
     * @param int $numberOfDays
     * @param PreUpdateEventArgs $args
     * @return bool
     */
    protected function timestampEnteredOrLeftPeriodOfLastXDays($numberOfDays, PreUpdateEventArgs $args)
    {
        if (!$args->hasChangedField('time')) {
            return false;
        }

        $now = new LocalTime();
        $ageOfOldDate = (new LocalTime($args->getOldValue('time')))->diff($now, true)->days;
        $ageOfNewDate = (new LocalTime($args->getNewValue('time')))->diff($now, true)->days;

        return (
            ($ageOfOldDate <= $numberOfDays && $ageOfNewDate > $numberOfDays) ||
            ($ageOfOldDate > $numberOfDays && $ageOfNewDate <= $numberOfDays)
        );
    }
}
