<?php

namespace Runalyze\Tests\Mathematics\Filter\Butterworth;

use Runalyze\Mathematics\Filter\Butterworth\Lowpass2ndOrderCoefficients;

class Lowpass2ndOrderCoefficientsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Values are compared to the following: (but we use different signs for ai and they don't normalize the frequency)
     * @see http://stackoverflow.com/questions/20924868/calculate-coefficients-of-2nd-order-butterworth-low-pass-filter
     */
    public function testSomeCoefficients()
    {
        $coefficients = new Lowpass2ndOrderCoefficients(0.05);
        $inputCoefficients = $coefficients->getInputCoefficients();
        $outputCoefficients = $coefficients->getOutputCoefficients();

        $this->assertEquals(0.06745, $inputCoefficients[0], '', 0.00001);
        $this->assertEquals(0.13491, $inputCoefficients[1], '', 0.00001);
        $this->assertEquals(0.06745, $inputCoefficients[2], '', 0.00001);

        $this->assertEquals(1.0, $outputCoefficients[0]);
        $this->assertEquals(-1.14298, $outputCoefficients[1], '', 0.00001);
        $this->assertEquals(0.41280, $outputCoefficients[2], '', 0.00001);
    }
}
