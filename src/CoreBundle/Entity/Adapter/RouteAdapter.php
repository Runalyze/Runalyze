<?php

namespace Runalyze\Bundle\CoreBundle\Entity\Adapter;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\ElevationCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Services\Import\ElevationCorrection;
use Runalyze\Parameter\Application\ElevationMethod;

class RouteAdapter
{
    /** @var Route */
    protected $Route;

    public function __construct(Route $route)
    {
        $this->Route = $route;
    }

    public function correctElevation(ElevationCorrection $elevationCorrection)
    {
        if ($this->Route->hasGeohashes()) {
            list($latitudes, $longitudes) = $this->Route->getLatitudesAndLongitudes();
            $altitudeData = $elevationCorrection->loadAltitudeData($latitudes, $longitudes);

            if (null !== $altitudeData) {
                $this->Route->setElevationsCorrected($altitudeData);
                $this->Route->setElevationsSource($this->getStrategyName($elevationCorrection->getLastSuccessfulStrategy()));
            }
        }
    }

    /**
     * @param ElevationMethod $method
     * @param int $threshold [m]
     */
    public function calculateElevation(ElevationMethod $method, $threshold)
    {
        $calculator = new ElevationCalculator();
        $calculator->calculateFor($this->Route, $method, $threshold);
    }

    /**
     * @param object $object
     *
     * @return string
     */
    protected function getStrategyName($object)
    {
        $fullClassName = get_class($object);

        return substr($fullClassName, strrpos($fullClassName, '\\')+1);
    }
}
