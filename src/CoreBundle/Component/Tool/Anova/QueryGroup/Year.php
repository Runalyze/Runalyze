<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;

class Year implements QueryGroupInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $as
     * @param string $sportAlias
     */
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as, $sportAlias)
    {
        $queryBuilder->addSelect(sprintf('YEAR(FROM_UNIXTIME(%s.time)) as %s', $alias, $as));
    }

    /**
     * @param EntityManager $entityManager
     * @param Account $account
     * @param AnovaData $anovaData
     * @return array
     */
    public function loadAllGroups(EntityManager $entityManager, Account $account, AnovaData $anovaData)
    {
        $startYear = (int)$anovaData->getDateFrom()->format('Y');
        $endYear = (int)$anovaData->getDateTo()->format('Y');
        $years = [];

        for ($year = $startYear; $year <= $endYear; ++$year) {
            $years[$year] = (string)$year;
        }

        return $years;
    }

    /**
     * @return bool
     */
    public function showEmptyGroups()
    {
        return true;
    }
}
