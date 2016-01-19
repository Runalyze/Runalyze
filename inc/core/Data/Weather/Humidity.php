<?php
/**
 * This file contains class::Humidity
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

use Runalyze\Activity\ValueInterface;

/**
 * Humidity
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */
class Humidity implements ValueInterface
{
    /**
     * Humidity in percent
     * @var float|null
     */
    protected $Percent;

    /**
     * Construct Humidity
     * @param float|null $percent
     */
    public function __construct($percent = null)
    {
        $this->set($percent);
    }

    /**
     * Set humidity
     * @param float|null $percent
     * @return \Runalyze\Data\Weather\Humidity $this-reference
     * @throws \InvalidArgumentException
     */
    public function set($percent)
    {
        if (null !== $percent && (!is_numeric($percent) || $percent < 0 || $percent > 100)) {
            throw new \InvalidArgumentException('Humidity must be a numeric value between 0 and 100.');
        }

        $this->Percent = $percent;

        return $this;
    }

    /**
     * Label for value
     * @return string
     */
    public function label()
    {
        return __('Humidity');
    }

    /**
     * Label for value
     * @return string
     */
    public function unit()
    {
        return '&#37;';
    }

    /**
     * Value
     * @return null|int
     */
    public function value()
    {
        return $this->Percent;
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

        return round($this->Percent).($withUnit ? '&nbsp;'.$this->unit() : '');
    }

    /**
     * Humidity is unknown?
     * @return bool
     */
    public function isUnknown()
    {
        return (null === $this->Percent);
    }
}