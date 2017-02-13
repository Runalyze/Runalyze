<?php

namespace Runalyze\Calculation\Route;

use Runalyze\Calculation\Math\MovingAverage\Kernel\AbstractKernel;
use Runalyze\Calculation\Math\MovingAverage\WithKernel;
use Runalyze\Mathematics\Numerics\Derivative;
use Runalyze\Model;

class Gradient
{
	/** @var array */
	protected $Elevation = [];

    /** @var array */
    protected $Distance = [];

    /** @var array  */
    protected $Gradient = [];

    /** @var AbstractKernel|null */
    protected $MovingAverageKernel = null;

	/**
     * @param array $elevation
     * @param array $distance
	 */
	public function __construct(array $elevation = [], array $distance = [])
    {
        if (!empty($elevation) && !empty($distance)) {
            $this->setData($elevation, $distance);
        }
	}

    /**
     * @param array $elevation
     * @param array $distance
     * @throws \InvalidArgumentException
     */
    public function setData(array $elevation, array $distance)
    {
        if (count($elevation) !== count($distance)) {
            throw new \InvalidArgumentException('Input arrays must be of same size.');
        }

        $this->Elevation = $elevation;
        $this->Distance = $distance;
    }

    /**
     * @param Model\Route\Entity $route
     * @param Model\Trackdata\Entity $trackdata
     * @throws \InvalidArgumentException
     */
    public function setDataFrom(Model\Route\Entity $route, Model\Trackdata\Entity $trackdata)
    {
        if (!$route->hasElevations() || !$trackdata->has(Model\Trackdata\Entity::DISTANCE)) {
            throw new \InvalidArgumentException('Route must have elevations and trackdata must have distances.');
        }

        $this->Elevation = $route->elevations();
        $this->Distance = $trackdata->distance();
    }

    /**
     * @param AbstractKernel $kernel the kernel will be applied to elevation data only without using distance as index
     */
    public function setMovingAverageKernel(AbstractKernel $kernel)
    {
        $this->MovingAverageKernel = $kernel;
    }

    public function calculate()
    {
        if (empty($this->Elevation)) {
            return;
        }

        $elevation = $this->Elevation;

        $this->applyMovingAverage($elevation);

        $this->Gradient = array_map(function ($value) {
            return $value / 10;
        }, (new Derivative())->calculate(
            $elevation,
            $this->Distance
        ));
    }

    /**
     * @param array $elevation
     */
    protected function applyMovingAverage(array &$elevation)
    {
        if (null !== $this->MovingAverageKernel) {
            $movingAverage = new WithKernel($elevation);
            $movingAverage->setKernel($this->MovingAverageKernel);
            $movingAverage->calculate();

            $elevation = $movingAverage->movingAverage();
        }
    }

    /**
     * @return array
     */
    public function getSeries()
    {
        return $this->Gradient;
    }
}
