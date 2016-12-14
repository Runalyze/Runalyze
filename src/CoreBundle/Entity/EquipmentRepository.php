<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class EquipmentRepository extends EntityRepository
{
    /**
     * @param int $equipmentTypeId
     * @param Account $account
     * @return Equipment[]
     */
    public function findByTypeId($equipmentTypeId, Account $account)
    {
        return $this->findBy([
            'type' => $equipmentTypeId,
            'account' => $account->getId()
        ]);
    }
}
