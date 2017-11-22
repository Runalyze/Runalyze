<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Calculation\Elevation\Calculator;
use Runalyze\Parameter\Application\ElevationMethod;

class ElevationCalculator
{
    /** @var ElevationMethod */
    protected $ElevationMethod;

    /** @var int [m] */
    protected $Threshold;

    /** @var Route */
    protected $Route;

    public function calculateFor(Route $route, ElevationMethod $method, $threshold)
    {
        $this->Route = $route;
        $this->ElevationMethod = $method;
        $this->Threshold = $threshold;

        if ($this->Route->hasElevations()) {
            $calculator = new Calculator($route->getElevations(), $this->ElevationMethod, $this->Threshold);
            $calculator->calculate();

            $this->setElevationValues($route, $calculator);
        } else {
            $this->setElevationValuesToNull($route);
        }
    }

    protected function setElevationValuesToNull(Route $route)
    {
        $route->setElevation(0);
        $route->setElevationUp(0);
        $route->setElevationDown(0);
    }

    protected function setElevationValues(Route $route, Calculator $calculator)
    {
        $route->setElevation($calculator->totalElevation());
        $route->setElevationUp($calculator->elevationUp());
        $route->setElevationDown($calculator->elevationDown());
    }
}
