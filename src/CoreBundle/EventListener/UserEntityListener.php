<?php

namespace Runalyze\Bundle\CoreBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\User;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationUpdater;

class UserEntityListener
{
    public function postPersist(User $user, LifecycleEventArgs $args)
    {
        $this->updateHeartRateStatsInDataConfiguration($args, $user->getAccount());
    }

    public function postUpdate(User $user, LifecycleEventArgs $args)
    {
        $this->updateHeartRateStatsInDataConfiguration($args, $user->getAccount());
    }

    public function postRemove(User $user, LifecycleEventArgs $args)
    {
        $this->updateHeartRateStatsInDataConfiguration($args, $user->getAccount());
    }

    protected function updateHeartRateStatsInDataConfiguration(LifecycleEventArgs $args, Account $account)
    {
        $entityManager = $args->getEntityManager();
        $userRepository = $entityManager->getRepository('CoreBundle:User');
        $configurationUpdater = new ConfigurationUpdater($entityManager->getRepository('CoreBundle:Conf'));

        if ($entityManager->getUnitOfWork()->isScheduledForDelete($account)) {
            return;
        }

        $restingHeartRate = $userRepository->getCurrentRestingHeartRate($account) ?: 60;
        $maximalHeartRate = $userRepository->getCurrentMaximalHeartRate($account) ?: 200;

        $configurationUpdater->updateRestingHeartRate($account, $restingHeartRate);
        $configurationUpdater->updateMaximalHeartRate($account, $maximalHeartRate);
    }
}
