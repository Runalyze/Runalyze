<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Calculation\Math\MovingAverage\Kernel\AbstractKernel;
use Runalyze\Calculation\Route\Gradient;

class GradientCalculator
{
    public function calculateFor(Trackdata $trackData, Route $route, AbstractKernel $kernel)
    {
        if (!$trackData->hasDistance() || !$route->hasElevations()) {
            $trackData->setGradient(null);

            return;
        }

        $gradient = new Gradient(
            $route->getElevations(),
            $trackData->getDistance()
        );
        $gradient->setMovingAverageKernel($kernel);
        $gradient->calculate();

        $trackData->setGradient($gradient->getSeries());
    }
}
