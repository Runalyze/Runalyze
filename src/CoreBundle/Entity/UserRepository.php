<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return User[]
     */
    public function findAllFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ], [
            'time' => 'DESC'
        ]);
    }

    /**
     * @param User $user
     * @param Account $account
     */
    public function remove(User $user, Account $account)
    {
        $this->_em->remove($user);
        $this->_em->flush($user);

        $this->resetLegacyCache();
    }

    protected function resetLegacyCache()
    {
        \Cache::delete(\UserData::CACHE_KEY);
        \Helper::recalculateHFmaxAndHFrest();
    }
}
