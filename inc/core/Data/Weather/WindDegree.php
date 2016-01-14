<?php
/**
 * This file contains class::WindDegree
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

use Runalyze\Activity\ValueInterface;

/**
 * Wind Degree
 *
 * @author Hannes Christiansen
 * @author Michael
 * @package Runalyze\Data\Weather
 */
class WindDegree implements ValueInterface
{
    /**
     * Wind Degree
     * @var float|null [°]
     */
    protected $Value;

    /**
     * Wind Degree
     * @param float|null $degrees
     */
    public function __construct($degrees = null)
    {
        $this->set($degrees);
    }

    /**
     * Set wind Degree
     * @param float|null $degrees
     * @return \Runalyze\Data\Weather\WindDegree
     * @throws \InvalidArgumentException
     */
    public function set($degrees)
    {
        if (null !== $degrees && !is_numeric($degrees)) {
            throw new \InvalidArgumentException('Value must be numeric.');
        }

        $this->Value = $degrees;

        return $this;
    }

    /**
     * Label for value
     * @return string
     */
    public function label()
    {
        return __('Wind Degree');
    }

    /**
     * Label for value
     * @return string
     */
    public function unit()
    {
        return '&deg;';
    }

    /**
     * Value
     * @return null|float
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

        return round($this->Value).($withUnit ? $this->unit() : '');
    }

    /**
     * Wind Speed is unknown?
     * @return bool
     */
    public function isUnknown()
    {
        return (null === $this->Value);
    }
}
