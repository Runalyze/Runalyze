<?php

namespace Runalyze\Mathematics\Scale;

/**
 * Two-part percental scale
 *
 * This linear scale is made of two linear parts, split at the inflection point that will be mapped to 50%.
 */
class TwoPartPercental extends Percental
{
    /** @var float */
    protected $InflectionPoint = 50.0;

    /**
     * @param float $min
     * @param float $inflectionPoint
     * @param float $max
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($min = 0.0, $inflectionPoint = 50.0, $max = 100.0)
    {
        parent::__construct($min, $max);

        $this->setInflectionPoint($inflectionPoint);
    }

    /**
     * @param float $inflectionPoint
     *
     * @throws \InvalidArgumentException
     */
    public function setInflectionPoint($inflectionPoint)
    {
        if (!is_numeric($inflectionPoint)) {
            throw new \InvalidArgumentException('Given maximum must be numeric.');
        }

        $this->InflectionPoint = (float)$inflectionPoint;
    }

    public function transform($input)
    {
        if ($input < $this->InflectionPoint) {
            $value = ($input - $this->Min) * 50.0 / ($this->InflectionPoint - $this->Min);
        } else {
            $value = 50.0 + ($input - $this->InflectionPoint) * 50.0 / ($this->Max - $this->InflectionPoint);
        }

        return min(100.0, max(0.0, $value));
    }
}
