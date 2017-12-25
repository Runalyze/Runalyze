<?php

namespace Runalyze\Bundle\CoreBundle\Entity\Adapter;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\ElevationCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Route;
use Runalyze\Bundle\CoreBundle\Services\Import\ElevationCorrection;
use Runalyze\Parameter\Application\ElevationMethod;
use Runalyze\Service\ElevationCorrection\Strategy\StrategyInterface;

class RouteAdapter
{
    /** @var Route */
    protected $Route;

    public function __construct(Route $route)
    {
        $this->Route = $route;
    }

    /**
     * @param ElevationCorrection $elevationCorrection
     * @param StrategyInterface|null $strategy
     * @return bool true on success
     */
    public function correctElevation(ElevationCorrection $elevationCorrection, StrategyInterface $strategy = null)
    {
        if ($this->Route->hasGeohashes()) {
            list($latitudes, $longitudes) = $this->Route->getLatitudesAndLongitudes();
            $altitudeData = $elevationCorrection->loadAltitudeData($latitudes, $longitudes, $strategy);

            if (null !== $altitudeData) {
                $this->Route->setElevationsCorrected($altitudeData);
                $this->Route->setElevationsSource($this->getStrategyName($elevationCorrection->getLastSuccessfulStrategy()));

                return true;
            }
        }

        return false;
    }

    public function removeElevation()
    {
        $this->Route->setElevationsCorrected(null);
        $this->Route->setElevationsSource(null);
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
