<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Entity\Account;

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
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport[] $sports
     * @return array
     */
    public function loadAllGroups(EntityManager $entityManager, Account $account, array $sports)
    {
        $groups = [];

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
}
