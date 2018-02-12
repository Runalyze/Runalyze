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
     * @param string|string[] $equipmentName
     * @param Account $account
     * @return Equipment[]
     */
    public function findByName($equipmentName, Account $account)
    {
        return $this->findBy([
            'account' => $account->getId(),
            'name' => $equipmentName
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

    /**
     * @param Equipment[] $equipment
     * @param int|float $additionalDuration [s]
     * @param float $additionalDistance [km]
     * @param bool $flush
     */
    public function updateEquipment(array $equipment, $additionalDuration, $additionalDistance = 0.0, $flush = true)
    {
        foreach ($equipment as $object) {
            $object->addTime($additionalDuration);
            $object->addDistance($additionalDistance);

            $this->_em->persist($object);
        }

        if ($flush) {
            $this->_em->flush($equipment);
        }
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
