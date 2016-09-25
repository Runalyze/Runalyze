<?php

namespace Runalyze\Calculation\Math;

/**
 * Calculate array-wise dervivative of f(x) = y
 *
 * The derivate of f(x) given at discrete points x_i is calculated by
 *      d/dx f(x_i) = (f(x_i) - f(x_i-1)) / (x_i - x_i-1)
 * for i > 0 and
 *      d/dx f(x_0) = d/dx f(x_1)
 */
class Derivative
{
    /**
     * @param array $y
     * @param array $x
     * @return array
     */
    public function calculate(array $y, array $x)
    {
        if (count($x) !== $num = count($y)) {
            throw new \InvalidArgumentException('Input arrays must be of same size.');
        }

        if (0 === $num) {
            throw new \InvalidArgumentException('Input arrays must not be empty.');
        }

        $ddx = [];

        for ($i = 1; $i < $num; ++$i) {
            $ddx[] = ($y[$i] - $y[$i - 1]) / ($x[$i] - $x[$i - 1]);
        }

        array_unshift($ddx, $ddx[0]);

        return $ddx;
    }
}
