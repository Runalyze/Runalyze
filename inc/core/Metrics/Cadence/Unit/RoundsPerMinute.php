<?php

namespace Runalyze\Metrics\Cadence\Unit;

use Runalyze\Metrics\Common\BaseUnitTrait;

class RoundsPerMinute extends AbstractCadenceUnit
{
    use BaseUnitTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'rpm';
    }
}
