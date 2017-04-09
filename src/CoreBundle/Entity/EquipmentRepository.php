<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Runalyze\Bundle\CoreBundle\Model\Equipment\EquipmentStatistics;

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

    /**
     * @param int $typeId
     * @param Account $account
     * @return EquipmentStatistics
     */
    public function getStatisticsForType($typeId, Account $account)
    {
        return new EquipmentStatistics($this->createQueryBuilder('eq')
            ->select('eq')
            ->addSelect('SUM(1) as num')
            ->addSelect('MIN(t.s/t.distance) as max_pace')
            ->addSelect('MAX(t.distance) as max_distance')
            ->join('eq.activity', 't')
            ->where('eq.type = :typeid')
            ->andWhere('eq.account = :account')
            ->setParameter(':typeid', $typeId)
            ->setParameter(':account', $account->getId())
            ->addOrderBy('eq.distance', 'DESC')
            ->groupBy('eq.id')
            ->getQuery()
            ->getResult()
        );
    }

    public function save(Equipment $equipment)
    {
        $this->_em->persist($equipment);
        $this->_em->flush();
    }

    public function remove(Equipment $equipment)
    {
        $this->_em->remove($equipment);
        $this->_em->flush();
    }
}
