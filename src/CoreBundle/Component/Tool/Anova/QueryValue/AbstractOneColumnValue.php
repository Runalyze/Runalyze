<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractOneColumnValue implements QueryValueInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $as
     */
    public function addSelectionToQuery(QueryBuilder $queryBuilder, $alias, $as)
    {
        $queryBuilder
            ->addSelect(sprintf('%s.%s as %s', $alias, $this->getColumn(), $as))
            ->andWhere(sprintf('%s.%s IS NOT NULL', $alias, $this->getColumn()));
    }

    /**
     * @return string
     */
    abstract protected function getColumn();
}
