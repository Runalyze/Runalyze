<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\GroundContactBalance\Unit\AbstractGroundContactBalanceUnit;
use Runalyze\Metrics\GroundContactBalance\Unit\PercentLeft;

class GroundContactBalance extends AbstractOneColumnValue
{
    /**
     * @return string
     */
    protected function getColumn()
    {
        return 'groundcontactBalance';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return AbstractGroundContactBalanceUnit
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new PercentLeft();
    }
}
