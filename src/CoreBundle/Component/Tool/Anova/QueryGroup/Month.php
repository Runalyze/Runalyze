<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;

class Month implements QueryGroupInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $as
     * @param string $sportAlias
     */
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as, $sportAlias)
    {
        $queryBuilder->addSelect(sprintf('MONTH(FROM_UNIXTIME(%s.time)) as %s', $alias, $as));
    }

    /**
     * @param EntityManager $entityManager
     * @param Account $account
     * @param AnovaData $anovaData
     * @return array
     */
    public function loadAllGroups(EntityManager $entityManager, Account $account, AnovaData $anovaData)
    {
        return $this->filterMonths([
            1 => __('January'),
            2 => __('February'),
            3 => __('March'),
            4 => __('April'),
            5 => __('May'),
            6 => __('June'),
            7 => __('July'),
            8 => __('August'),
            9 => __('September'),
            10 => __('October'),
            11 => __('November'),
            12 => __('December')
        ], $anovaData->getDateFrom(), $anovaData->getDateTo());
    }

    /**
     * @param array $months
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return array
     */
    protected function filterMonths(array $months, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $startYear = (int)$dateFrom->format('Y');
        $endYear = (int)$dateTo->format('Y');

        if ($startYear < $endYear - 1) {
            return $months;
        }

        $startMonth = (int)$dateFrom->format('m');
        $endMonth = (int)$dateTo->format('m');

        if ($startYear == $endYear - 1) {
            return
                array_slice($months, $startMonth - 1, 13 - $startMonth, true) +
                array_slice($months, 0, $endMonth, true);
        }

        return array_slice($months, $startMonth - 1, $endMonth - $startMonth + 1, true);
    }

    /**
     * @return bool
     */
    public function showEmptyGroups()
    {
        return true;
    }
}
