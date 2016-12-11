<?php

namespace Runalyze\Metrics\Time\Unit;

use Runalyze\Metrics\Common\BaseUnitTrait;

class Seconds extends AbstractTimeUnit
{
    use BaseUnitTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 's';
    }
}
