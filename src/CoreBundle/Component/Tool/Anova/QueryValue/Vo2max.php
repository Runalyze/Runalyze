<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\Factorial;

class Vo2max extends AbstractOneColumnValue
{
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as)
    {
        parent::addSelectionToQuery($queryBuilder, $alias, $as);

        $queryBuilder->andWhere(sprintf('%s.%s = 1', $alias, 'useVO2max'));
    }

    protected function getColumn()
    {
        return 'vo2max';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return Factorial
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return $unitSystem->getVO2maxUnit();
    }
}
