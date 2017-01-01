<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;

class EquipmentType implements QueryGroupInterface
{
    /** @var int */
    protected $EquipmentTypeId;

    public function __construct($id)
    {
        $this->EquipmentTypeId = (int) $id;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $as
     * @param string $sportAlias
     */
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as, $sportAlias)
    {
        $queryBuilder
            ->addSelect(sprintf('%s.id as %s', 'eq', $as))
            ->leftJoin(sprintf('%s.equipment', $alias), 'eq')
            ->andWhere('eq.type = :equipmentTypeId')
            ->setParameter(':equipmentTypeId', $this->EquipmentTypeId)
            ->distinct()
        ;
    }

    /**
     * @param EntityManager $entityManager
     * @param Account $account
     * @param AnovaData $anovaData
     * @return array
     */
    public function loadAllGroups(EntityManager $entityManager, Account $account, AnovaData $anovaData)
    {
        $groups = [];

        foreach ($entityManager->getRepository('CoreBundle:Equipment')->findByTypeId($this->EquipmentTypeId, $account) as $equipment) {
            $groups[$equipment->getId()] = $equipment->getName();
        }

        return $groups;
    }

    /**
     * @return bool
     */
    public function showEmptyGroups()
    {
        return false;
    }
}
