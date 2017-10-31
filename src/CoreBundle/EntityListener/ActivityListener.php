<?php

namespace Runalyze\Bundle\CoreBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\PowerCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Account;
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

    public function __construct(RecalculationManager $recalculationManager, ConfigurationManager $configurationManager)
    {
        $this->RecalculationManager = $recalculationManager;
        $this->ConfigurationManager = $configurationManager;
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
        $this->calculatePower($activity);
    }

    public function postPersist(Training $activity, LifecycleEventArgs $args)
    {
        $this->scheduleRunningRelatedRecalculationsIfRequired($activity);
        $this->addStartTimeCheck($activity->getAccount(), $activity->getTime(), false);
    }

    public function preUpdate(Training $activity, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('sport')) {
            $this->calculatePower($activity);
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

    public function postUpdate(Training $activity, LifecycleEventArgs $args)
    {
    }

    public function postRemove(Training $activity, LifecycleEventArgs $args)
    {
        $this->scheduleRunningRelatedRecalculationsIfRequired($activity);

        $this->addStartTimeCheck($activity->getAccount(), $activity->getTime(), true);
    }

    protected function calculatePower(Training $activity)
    {
        $activity->getAdapter()->calculatePower();
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
