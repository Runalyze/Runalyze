<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TypeRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @param Sport|null $sport
     * @return Type[]
     */
    public function findAllFor(Account $account, Sport $sport = null)
    {
        if (null !== $sport) {
            return $this->findBy([
                'account' => $account->getId(),
                'sport' => $sport->getId()
            ]);
        }

        return $this->findBy([
            'account' => $account->getId()
        ]);
    }

    public function save(Type $type)
    {
        $this->_em->persist($type);
        $this->_em->flush();
    }
}
