<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Velocity\Unit\AbstractPaceUnit;

class Pace implements QueryValueInterface
{
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as)
    {
        $queryBuilder
            ->addSelect(sprintf('%s.s/%s.distance as %s', $alias, $alias, $as))
            ->andWhere(sprintf('%s.distance IS NOT NULL', $alias));
    }

    /**
     * @param UnitSystem $unitSystem
     * @return AbstractPaceUnit
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return $unitSystem->getPaceUnit();
    }
}
