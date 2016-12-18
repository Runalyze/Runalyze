<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;

class Sport implements QueryGroupInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $as
     * @param string $sportAlias
     */
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as, $sportAlias)
    {
        $queryBuilder->addSelect(sprintf('%s.id as %s', $sportAlias, $as));
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

        foreach ($entityManager->getRepository('CoreBundle:Sport')->findAllFor($account) as $sport) {
            foreach ($sports as $singleSport) {
                if ($singleSport->getId() == $sport->getId()) {
                    $groups[$sport->getId()] = $sport->getName();
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
