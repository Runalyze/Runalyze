<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\UnitInterface;

interface QueryValueInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $as
     */
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as);

    /**
     * @param UnitSystem $unitSystem
     * @return UnitInterface
     */
    public function getValueUnit(UnitSystem $unitSystem);
}
