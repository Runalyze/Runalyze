<?php

namespace Runalyze\Calculation\Activity;

use Runalyze\Calculation\JD;
use Runalyze\Calculation\Elevation;
use Runalyze\Calculation\Trimp;
use Runalyze\Context;
use Runalyze\Configuration;
use Runalyze\Model;

class Calculator
{
    /** @var Model\Activity\Entity */
    protected $Activity;

    /** @var Model\Trackdata\Entity */
    protected $Trackdata;

    /** @var Model\Route\Entity */
    protected $Route;

    /**
     * @param Model\Activity\Entity $activity
     * @param null|Model\Trackdata\Entity $trackdata
     * @param null|Model\Route\Entity $route
     */
    public function __construct(
        Model\Activity\Entity $activity,
        Model\Trackdata\Entity $trackdata = null,
        Model\Route\Entity $route = null
    )
    {
        $this->Activity = $activity;
        $this->Trackdata = $trackdata;
        $this->Route = $route;
    }

    /**
     * @return bool
     */
    protected function knowsTrackdata()
    {
        return (null !== $this->Trackdata);
    }

    /**
     * @return bool
     */
    protected function knowsRoute()
    {
        return (null !== $this->Route);
    }

    /**
     * @return float [ml/kg/min]
     */
    public function estimateVO2maxByTime()
    {
        $VO2max = new JD\LegacyEffectiveVO2max;
        $VO2max->fromPace($this->Activity->distance(), $this->Activity->duration());

        return $VO2max->uncorrectedValue();
    }

    /**
     * @param float|null $distance [km]
     * @return float [ml/kg/min]
     */
    public function estimateVO2maxByHeartRate($distance = null)
    {
        if (null === $distance) {
            $distance = $this->Activity->distance();
        }

        $VO2max = new JD\LegacyEffectiveVO2max;
        $VO2max->fromPaceAndHR(
            $distance,
            $this->Activity->duration(),
            $this->Activity->hrAvg() / Configuration::Data()->HRmax()
        );

        return $VO2max->value();
    }

    /**
     * @return float [ml/kg/min]
     */
    public function estimateVO2maxByHeartRateWithElevation()
    {
        if ($this->knowsRoute()) {
            if ($this->Route->elevationUp() > 0 || $this->Route->elevationDown() > 0) {
                return $this->estimateVO2maxByHeartRateWithElevationFor($this->Route->elevationUp(), $this->Route->elevationDown());
            }

            return $this->estimateVO2maxByHeartRateWithElevationFor($this->Route->elevation(), $this->Route->elevation());
        }

        return $this->estimateVO2maxByHeartRateWithElevationFor($this->Activity->elevation(), $this->Activity->elevation());
    }

    /**
     * @param int $up
     * @param int $down
     * @return float
     */
    public function estimateVO2maxByHeartRateWithElevationFor($up, $down)
    {
        $Modifier = new Elevation\DistanceModifier(
            $this->Activity->distance(),
            $up,
            $down,
            Configuration::VO2max()
        );

        return $this->estimateVO2maxByHeartRate($Modifier->correctedDistance());
    }

    /**
     * @return int
     */
    public function calculateTrimp()
    {
        if ($this->knowsTrackdata() && $this->Trackdata->has(Model\Trackdata\Entity::HEARTRATE)) {
            $Collector = new Trimp\DataCollector($this->Trackdata->heartRate(), $this->Trackdata->time());
            $data = $Collector->result();
        } elseif ($this->Activity->hrAvg() > 0) {
            $data = array($this->Activity->hrAvg() => $this->Activity->duration());
        } else {
            $Factory = Context::Factory();

            if ($this->Activity->typeid() > 0) {
                $data = array($Factory->type($this->Activity->typeid())->hrAvg() => $this->Activity->duration());
            } else {
                $data = array($Factory->sport($this->Activity->sportid())->avgHR() => $this->Activity->duration());
            }
        }

        $Athlete = Context::Athlete();
        $Calculator = new Trimp\Calculator($Athlete, $data);

        return round($Calculator->value());
    }
}
