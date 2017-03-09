<?php

namespace Runalyze\Bundle\CoreBundle\Doctrine\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator, PDO;

class ColumnHydrator extends AbstractHydrator
{
    /**
     * @return array
     */
    protected function hydrateAllData()
    {
        return $this->_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
