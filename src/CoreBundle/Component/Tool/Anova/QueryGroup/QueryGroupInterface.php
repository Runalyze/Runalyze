<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Entity\Account;

interface QueryGroupInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $as
     * @param string $sportAlias
     */
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as, $sportAlias);

    /**
     * @param EntityManager $entityManager
     * @param Account $account
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport[] $sports
     * @return array
     */
    public function loadAllGroups(EntityManager $entityManager, Account $account, array $sports);
}
