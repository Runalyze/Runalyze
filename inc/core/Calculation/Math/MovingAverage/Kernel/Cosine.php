<?php
/**
 * This file contains class::Cosine
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

/**
 * Cosine kernel
 *
 * @see https://en.wikipedia.org/wiki/Kernel_(statistics)#Kernel_functions_in_common_use
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */
class Cosine extends AbstractKernel
{
    /**
     * @inheritdoc
     * @param float $width
     */
    public function __construct($width)
    {
        $this->NormalizationFactor = M_PI / 2.0;

        parent::__construct($width);
    }

    /**
     * @param float $u
     * @return float
     */
    public function atTransformed($u)
    {
        if (-1.0 <= $u && $u <= 1.0) {
            return cos($u * M_PI / 2.0);
        }

        return 0.0;
    }
}