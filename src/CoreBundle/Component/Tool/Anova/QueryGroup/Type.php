<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;

class Type implements QueryGroupInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $as
     * @param string $sportAlias
     */
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as, $sportAlias)
    {
        $queryBuilder
            ->addSelect(sprintf('%s.id as %s', 'ty', $as))
            ->join(sprintf('%s.type', $alias), 'ty')
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
        $sports = $anovaData->getSport();

        foreach ($entityManager->getRepository('CoreBundle:Type')->findAllFor($account) as $type) {
            foreach ($sports as $sport) {
                if ($sport->getId() == $type->getSport()->getId()) {
                    $groups[$type->getId()] = $type->getName();
                    break;
                }
            }
        }

        return $groups;
    }

    /**
     * @return bool
     */
    public function showEmptyGroups()
    {
        return true;
    }
}
