<?php

namespace Runalyze\Mathematics\Scale;

/**
 * Percental scale
 *
 * This linear scale will map a given minimum to 0 and a given maximum to 100.
 */
class Percental implements ScaleInterface
{
    /** @var float */
    protected $Min = 0.0;

    /** @var float */
    protected $Max = 100.0;

    /**
     * @param float $min
     * @param float $max
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($min = 0.0, $max = 100.0)
    {
        $this->setMinimum($min);
        $this->setMaximum($max);
    }

    /**
     * @param float $min
     *
     * @throws \InvalidArgumentException
     */
    public function setMinimum($min)
    {
        if (!is_numeric($min)) {
            throw new \InvalidArgumentException('Given minimum must be numeric.');
        }

        $this->Min = (float)$min;
    }

    /**
     * @param float $max
     *
     * @throws \InvalidArgumentException
     */
    public function setMaximum($max)
    {
        if (!is_numeric($max)) {
            throw new \InvalidArgumentException('Given maximum must be numeric.');
        }

        $this->Max = (float)$max;
    }

    public function transform($input)
    {
        if ($this->Max == $this->Min) {
            return 0.0;
        }

        return min(100.0, 100.0 * max(0.0, $input - $this->Min) / ($this->Max - $this->Min));
    }
}
