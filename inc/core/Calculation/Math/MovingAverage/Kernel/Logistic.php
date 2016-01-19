<?php
/**
 * This file contains class::Logistic
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

/**
 * Logistic kernel
 *
 * @see https://en.wikipedia.org/wiki/Kernel_(statistics)#Kernel_functions_in_common_use
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */
class Logistic extends AbstractKernel
{
    /** @var float */
    protected $DefaultWidth = 10.0;

    /** @var float */
    protected $NormalizationFactor = 10.0;

    /**
     * @param float $u
     * @return float
     */
    public function atTransformed($u)
    {
        return 1.0 / (exp($u) + 2.0 + exp(-$u));
    }
}