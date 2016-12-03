<?php

namespace Runalyze\Mathematics\Filter\Butterworth;

class Lowpass2ndOrderCoefficients extends AbstractButterworthCoefficients
{
    /**
     * @param double $normalizedFrequency
     */
    protected function calculateCoefficients($normalizedFrequency)
    {
        $ita = 1.0 / tan(M_PI * 2.0 * $normalizedFrequency);
        $q = sqrt(2.0);

        $this->InputCoefficients[0] = 1.0 / (1.0 + $q * $ita + $ita * $ita);
        $this->InputCoefficients[1] = 2.0 * $this->InputCoefficients[0];
        $this->InputCoefficients[2] = $this->InputCoefficients[0];

        $this->OutputCoefficients[1] = - 2.0 * ($ita * $ita - 1.0) * $this->InputCoefficients[0];
        $this->OutputCoefficients[2] = (1.0 - $q * $ita + $ita * $ita) * $this->InputCoefficients[0];
    }
}
