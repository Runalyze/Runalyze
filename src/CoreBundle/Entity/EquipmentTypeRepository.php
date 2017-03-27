<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class EquipmentTypeRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return EquipmentType[]
     */
    public function findSingleChoiceTypesFor(Account $account)
    {
        return $this->findBy([
            'input' => 0,
            'account' => $account->getId()
        ]);
    }

    /**
     * @param Account $account
     * @return EquipmentType[]
     */
    public function findAllFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ]);
    }
}
