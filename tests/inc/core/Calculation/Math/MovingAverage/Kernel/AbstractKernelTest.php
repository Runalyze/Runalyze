<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class AbstractKernelTest_MockTester extends AbstractKernel
{
    protected $DefaultWidth = 1.0;
    public function atTransformed($difference) { return $difference; }
}

class AbstractKernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testZeroKernelWidth()
    {
        new AbstractKernelTest_MockTester(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNegativeKernelWidth()
    {
        new AbstractKernelTest_MockTester(-1.23);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonNumericKernelWidth()
    {
        new AbstractKernelTest_MockTester('abc');
    }

    public function testConstructor()
    {
        $Object = new AbstractKernelTest_MockTester(1);

        $this->assertEquals(42, $Object->at(42));
    }

    public function testValuesAt()
    {
        $Object = new AbstractKernelTest_MockTester(1);

        $this->assertEquals([1, 2, 3, 3.14, 42, 1337], $Object->valuesAt([1, 2, 3, 3.14, 42, 1337]));
    }
}
