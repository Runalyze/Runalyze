<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ConfRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Conf[]
     */
    public function findByAccount(Account $account)
    {
        return $this->findBy([
            'accountid' => $account->getId()
        ]);
    }
}
