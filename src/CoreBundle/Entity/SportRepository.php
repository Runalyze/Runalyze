<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SportRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Sport[]
     */
    public function findAllFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ]);
    }

    /**
     * @param Account $account
     * @return Sport[]
     */
    public function findSportsWithKmFor(Account $account)
    {
        return $this->findBy(
            ['account' => $account->getId(), 'distances' => true]);
    }

}
