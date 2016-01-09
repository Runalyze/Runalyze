<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class KernelsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidKernel()
    {
        Kernels::get(-1, 5);
    }

    public function testAllConstructors()
    {
        foreach (Kernels::getEnum() as $kernelid) {
            Kernels::get($kernelid, 5.0);
        }
    }

    public function testNormalizations()
    {
        $x = range(-1.0, 1.0, 0.01);
        $num = count($x);

        foreach (Kernels::getEnum() as $kernelid) {
            $Kernel = Kernels::get($kernelid, 2.0);
            $sum = array_sum($Kernel->valuesAt($x, true));

            $this->assertEquals(1.0, $sum / $num, 'Kernel with id "'.$kernelid.'" is not normalized, sum is '.$sum, 0.02);
        }
    }
}
