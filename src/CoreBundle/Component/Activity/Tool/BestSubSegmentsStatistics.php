<?php

namespace Runalyze\Bundle\CoreBundle\Component\Activity\Tool;

use Runalyze\Calculation\Math\SubSegmentMaximization;
use Runalyze\Model\Trackdata;
use Runalyze\Model\Route\Entity as RouteEntity;

class BestSubSegmentsStatistics
{
    /** @var Trackdata\Entity */
    protected $Trackdata;

    /** @var SubSegmentMaximization|null */
    protected $DistanceSegments = null;

    /** @var SubSegmentMaximization|null */
    protected $TimeSegments = null;

    /** @var array */
    protected $Distances = [];

    /** @var array */
    protected $Times = [];

    /**
     * @param Trackdata\Entity $trackdata
     * @throws \InvalidArgumentException
     */
    public function __construct(Trackdata\Entity $trackdata)
    {
        if (!$trackdata->has(Trackdata\Entity::TIME) || !$trackdata->has(Trackdata\Entity::DISTANCE)) {
            throw new \InvalidArgumentException('Provided trackdata object must have time and distance array.');
        }

        $this->Trackdata = $trackdata;
    }

    /**
     * @param array $distances
     */
    public function setDistancesToAnalyze(array $distances)
    {
        $this->Distances = $distances;
    }

    /**
     * @param array $times
     */
    public function setTimesToAnalyze(array $times)
    {
        $this->Times = $times;
    }

    public function findSegments()
    {
        $num = $this->Trackdata->num();
        $time = $this->Trackdata->time();
        $distance = $this->Trackdata->distance();
        $timeAsDeltas = $time;
        $distanceAsDeltas = $distance;

        for ($i = 1; $i < $num; ++$i) {
            $timeAsDeltas[$i] = $time[$i] - $time[$i-1];
            $distanceAsDeltas[$i] = $distance[$i] - $distance[$i-1];
        }

        $this->DistanceSegments = new SubSegmentMaximization($timeAsDeltas, $distanceAsDeltas, $this->Distances);
        $this->DistanceSegments->minimize();

        $this->TimeSegments = new SubSegmentMaximization($distanceAsDeltas, $timeAsDeltas, $this->Times);
        $this->TimeSegments->maximize();
    }

    /**
     * @param RouteEntity $route
     * @return array
     */
    public function getDistanceSegmentPaths(RouteEntity $route)
    {
        return $this->getSegments($this->getDistanceSegments(), $route);
    }

    /**
     * @param RouteEntity $route
     * @return array
     */
    public function getTimeSegmentPaths(RouteEntity $route)
    {
        return $this->getSegments($this->getTimeSegments(), $route);
    }

    /**
     * @param SubSegmentMaximization $subSegmentMaximization
     * @param RouteEntity $route
     * @return array
     */
    private function getSegments(SubSegmentMaximization $subSegmentMaximization, RouteEntity $route)
    {
        $latLongs = $route->latitudesAndLongitudesFromGeohash();
        $seg = [];
        foreach($subSegmentMaximization->getAvailableSegmentLengths() as $index => $length) {
            $segIndices = $subSegmentMaximization->getIndizesOfMaximumForLengthIndex($index);
            $seg[$index] = [];
            for($i = $segIndices[0]; $i <= $segIndices[1]; $i++){
                $seg[$index][] = array($latLongs['lat'][$i], $latLongs['lng'][$i]);
            }

        }
        return $seg;
    }

    /**
     * @return null|SubSegmentMaximization
     */
    public function getDistanceSegments()
    {
        return $this->DistanceSegments;
    }

    /**
     * @return null|SubSegmentMaximization
     */
    public function getTimeSegments()
    {
        return $this->TimeSegments;
    }

    /**
     * @return array
     */
    public function getDistances()
    {
        return $this->Distances;
    }

    /**
     * @return array
     */
    public function getTimes()
    {
        return $this->Times;
    }
}
