<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\Simple;

class FlightTime implements QueryValueInterface
{
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as)
    {
        $queryBuilder
            ->addSelect(sprintf('(30000/%s.cadence - %s.groundcontact) as %s', $alias, $alias, $as))
            ->andWhere(sprintf('%s.cadence IS NOT NULL', $alias))
            ->andWhere(sprintf('%s.groundcontact IS NOT NULL', $alias));
    }

    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Simple('ms');
    }
}
