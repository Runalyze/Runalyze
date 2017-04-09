<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class EquipmentTypeRepository extends EntityRepository
{
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

    /**
     * @param Account $account
     * @return EquipmentType[]
     */
    public function findSingleChoiceTypesFor(Account $account)
    {
        return $this->findBy([
            'input' => EquipmentType::CHOICE_SINGLE,
            'account' => $account->getId()
        ]);
    }

    public function save(EquipmentType $equipmentType)
    {
        $this->_em->persist($equipmentType);
        $this->_em->flush();
    }

    public function remove(EquipmentType $equipmentType)
    {
        $this->_em->remove($equipmentType);
        $this->_em->flush();
    }
}
