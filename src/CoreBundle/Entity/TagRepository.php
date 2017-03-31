<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Tag[]
     */
    public function findAllFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ]);
    }

    public function save(Tag $tag)
    {
        $this->_em->persist($tag);
        $this->_em->flush();
    }
}
