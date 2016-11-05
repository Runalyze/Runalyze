<?php

namespace Runalyze\Metrics\Common;

abstract class AbstractMetric
{
    /**
     * @var mixed
     */
    protected $Value = null;

    /**
     * @return string full class name
     */
    abstract public function getBaseUnitClass();

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->Value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @return bool
     */
    public function isKnown()
    {
        return null !== $this->Value;
    }
}
