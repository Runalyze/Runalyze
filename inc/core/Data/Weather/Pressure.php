<?php
/**
 * This file contains class::Pressure
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

use Runalyze\Activity\ValueInterface;

/**
 * Pressure
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */
class Pressure implements ValueInterface
{
    /**
     * Pressure
     * @var float [hPa]
     */
    protected $Value;

    /**
     * Construct Pressure
     * @param float|null $hectoPascals [hPa]
     */
    public function __construct($hectoPascals = null)
    {
        $this->set($hectoPascals);
    }

    /**
     * Set Pressure
     * @param float|null $hectoPascals [hPa]
     * @return \Runalyze\Data\Weather\Pressure $this-reference
     * @throws \InvalidArgumentException
     */
    public function set($hectoPascals)
    {
        if (null !== $hectoPascals && !is_numeric($hectoPascals)) {
            throw new \InvalidArgumentException('Value must be numerical.');
        }

        $this->Value = $hectoPascals;

        return $this;
    }
    #
    /**
     * Label for value
     * @return string
     */
    public function label()
    {
        return __('Pressure');
    }

    /**
     * Label for value
     * @return string
     */
    public function unit()
    {
        return 'hpa';
    }

    /**
     * Value
     * @return null|float [hPa]
     */
    public function value()
    {
        return $this->Value;
    }

    /**
     * Format value as string
     * @param bool $withUnit
     * @return string
     */
    public function string($withUnit = true)
    {
        if ($this->isUnknown()) {
            return '';
        }

        return round($this->Value).($withUnit ? '&nbsp;'.$this->unit() : '');
    }

    /**
     * Pressure is unknown?
     * @return bool
     */
    public function isUnknown()
    {
        return (null === $this->Value);
    }
}