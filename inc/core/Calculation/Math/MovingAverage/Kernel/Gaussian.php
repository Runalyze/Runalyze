<?php
/**
 * This file contains class::Gaussian
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

/**
 * Gaussian kernel
 *
 * @see https://en.wikipedia.org/wiki/Kernel_(statistics)#Kernel_functions_in_common_use
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */
class Gaussian extends AbstractKernel
{
    /** @var float */
    protected $DefaultWidth = 6.0;

    /**
     * @inheritdoc
     * @param float $width
     */
    public function __construct($width)
    {
        $this->NormalizationFactor = 6.0 / sqrt(2.0 * M_PI);

        parent::__construct($width);
    }

    /**
     * @param float $u
     * @return float
     */
    public function atTransformed($u)
    {
        return exp(-0.5 * $u * $u);
    }
}