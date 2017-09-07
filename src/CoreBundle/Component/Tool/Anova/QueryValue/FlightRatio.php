<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\Factorial;

class FlightRatio implements QueryValueInterface
{
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as)
    {
        $queryBuilder
            ->addSelect(sprintf('(1 - %s.cadence * %s.groundcontact / 30000) as %s', $alias, $alias, $as))
            ->andWhere(sprintf('%s.cadence IS NOT NULL', $alias))
            ->andWhere(sprintf('%s.groundcontact IS NOT NULL', $alias));
    }

    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Factorial('%', 100, 1);
    }
}
