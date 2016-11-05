<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Metrics\Common\UnitInterface;
use Runalyze\Metrics\Pace\Unit\AbstractPaceInTimeFormatUnit;

class DisplayablePace extends DisplayableValue
{
    /**
     * @param mixed $value
     * @param AbstractPaceInTimeFormatUnit $unit
     */
    public function __construct($value, AbstractPaceInTimeFormatUnit $unit)
    {
        parent::__construct($value, $unit);
    }

    /**
     * @param bool|int $decimals
     * @param bool|string $decimalPoint
     * @param bool|string $thousandsSeparator
     * @return string value and unit, not separated by '&nbsp;'
     */
    public function getWithUnit($decimals = false, $decimalPoint = false, $thousandsSeparator = false)
    {
        return $this->getValue($decimals, $decimalPoint, $thousandsSeparator).$this->getUnit();
    }

    /**
     * @param bool|int $decimals
     * @param bool|string $decimalPoint
     * @param bool|string $thousandsSeparator
     * @return string
     */
    public function getValue($decimals = false, $decimalPoint = false, $thousandsSeparator = false)
    {
        $seconds = round($this->Value);
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        return $minutes.':'.str_pad($seconds, 2, '0', STR_PAD_LEFT);
    }
}
