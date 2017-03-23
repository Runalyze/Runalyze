<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Tag[]
     */
    public function findByAccount(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ]);
    }
}
