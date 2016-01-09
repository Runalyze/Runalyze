<?php
/**
 * This file contains class::AbstractKernel
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

/**
 * Abstract class for moving average kernels
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */
abstract class AbstractKernel
{
    /** @var float */
    private $TransformationFactor;

    /** @var float */
    protected $Width;

    /** @var float */
    protected $DefaultWidth = 2.0;

    /** @var float */
    protected $NormalizationFactor = 1.0;

    /**
     * AbstractKernel constructor
     * @param float $width
     * @throws \InvalidArgumentException
     */
    public function __construct($width)
    {
        if (!is_numeric($width) || $width <= 0.0) {
            throw new \InvalidArgumentException('Kernel width must be a positive float.');
        }

        $this->Width = (float)$width;
        $this->TransformationFactor = $this->DefaultWidth / $this->Width;
    }

    /**
     * @return float
     */
    final public function width()
    {
        return $this->Width;
    }

    /**
     * @param int|float $difference difference to X_0
     * @param bool $normalized flag if values should be normalized
     * @return float
     */
    final public function at($difference, $normalized = false)
    {
        if ($normalized) {
            return $this->NormalizationFactor * $this->atTransformed($this->TransformationFactor * $difference);
        }

        return $this->atTransformed($this->TransformationFactor * $difference);
    }

    /**
     * @param float $u
     * @return float
     */
    abstract protected function atTransformed($u);

    /**
     * @param array $differences
     * @param bool $normalized flag if values should be normalized
     * @return float[]
     */
    final public function valuesAt(array $differences, $normalized = false)
    {
        $values = array_map(
            array($this, 'atTransformed'),
            array_map(function($v) {
                return $this->TransformationFactor * $v;
            }, $differences)
        );

        if ($normalized) {
            $values = array_map(function($v) {
                return $this->NormalizationFactor * $v;
            }, $values);
        }

        return $values;
    }
}