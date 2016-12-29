<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Metrics\Common\UnitInterface;

class DisplayableValue
{
    /** @var mixed */
    protected $Value;

    /** @var string */
    protected $Unit;

    /** @var int */
    protected $DefaultDecimals;

    /** @var string */
    protected $DefaultDecimalPoint;

    /** @var string */
    protected $DefaultThousandsSeparator;

    /**
     * @param mixed $value
     * @param string|UnitInterface $unit
     * @param int $defaultDecimals
     * @param string $defaultDecimalPoint
     * @param string $defaultThousandsSeparator
     */
    public function __construct($value, $unit, $defaultDecimals = 0, $defaultDecimalPoint = '.', $defaultThousandsSeparator = ',')
    {
        if ($unit instanceof UnitInterface) {
            $value = $unit->fromBaseUnit($value);
            $unit = $unit->getAppendix();
        }

        $this->Value = $value;
        $this->Unit = $unit;
        $this->DefaultDecimals = $defaultDecimals;
        $this->DefaultDecimalPoint = $defaultDecimalPoint;
        $this->DefaultThousandsSeparator = $defaultThousandsSeparator;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getWithUnit();
    }

    /**
     * @param bool|int $decimals
     * @param bool|string $decimalPoint
     * @param bool|string $thousandsSeparator
     * @return string value and unit, separated by '&nbsp;'
     */
    public function getWithUnit($decimals = false, $decimalPoint = false, $thousandsSeparator = false)
    {
        return $this->getValue($decimals, $decimalPoint, $thousandsSeparator).'&nbsp;'.$this->getUnit();
    }

    /**
     * @param bool|int $decimals
     * @param bool|string $decimalPoint
     * @param bool|string $thousandsSeparator
     * @return string
     */
    public function getValue($decimals = false, $decimalPoint = false, $thousandsSeparator = false)
    {
        return number_format(
            $this->Value,
            false === $decimals ? $this->DefaultDecimals : $decimals,
            false === $decimalPoint ? $this->DefaultDecimalPoint : $decimalPoint,
            false === $thousandsSeparator ? $this->DefaultThousandsSeparator : $thousandsSeparator
        );
    }

    /**
     * @param $value
     * @return $this
     */
    public function setRawValue($value)
    {
        $this->Value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->Value;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->Unit;
    }

    /**
     * @return bool
     */
    public function isZero()
    {
        return 0 == $this->Value;
    }
}
