<?php

namespace Runalyze\Bundle\CoreBundle\Component\Statistics\MonthlyStats;

use Runalyze\Bundle\CoreBundle\Services\Selection\Selection;

class AnalysisSelection extends Selection
{
    /** @var string */
    const DISTANCE = 'km';

    /** @var string */
    const TIME = 's';

    /** @var string */
    const ELEVATION = 'em';

    /** @var string */
    const ENERGY = 'kcal';

    /** @var string */
    const TRIMP = 'trimp';

    /** @var string */
    const NUMBER = 'n';

    /**
     * @param mixed $currentKey
     */
    public function __construct($currentKey = null)
    {
        parent::__construct([
            self::DISTANCE => 'by distance',
            self::TIME => 'by time',
            self::ELEVATION => 'by elevation',
            self::ENERGY => 'by energy',
            self::TRIMP => 'by trimp',
            self::NUMBER => 'by number'
        ], $currentKey);
    }
}
