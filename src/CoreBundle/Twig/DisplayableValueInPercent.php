<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Metrics\Common\UnitInterface;

class DisplayableValueInPercent extends DisplayableValue
{
    /**
     * @param mixed $value
     * @param string|UnitInterface $unit
     * @param int $defaultDecimals
     * @param string $defaultDecimalPoint
     * @param string $defaultThousandsSeparator
     */
    public function __construct($value, $unit, $defaultDecimals = 0, $defaultDecimalPoint = '.', $defaultThousandsSeparator = ',')
    {
        parent::__construct($value, $unit, $defaultDecimals, $defaultDecimalPoint, $defaultThousandsSeparator);

        $this->Value *= 100;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return '%';
    }
}
