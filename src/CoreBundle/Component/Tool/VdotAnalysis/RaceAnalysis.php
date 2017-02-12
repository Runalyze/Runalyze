<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\VdotAnalysis;

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Calculation\JD\VDOTCorrector;
use Runalyze\Model;
use Runalyze\Sports\Running\Prognosis\Daniels;
use Runalyze\Util\LocalTime;

class RaceAnalysis
{
    /** @var float */
    protected $VdotFactor;

    /** @var float */
    protected $VdotShape;

    /** @var Model\Activity\Entity */
    protected $Activity;

    /**
     * @param Model\Activity\Entity $activity
     * @param float $vdotFactor
     * @param float $vdotShape
     */
    public function __construct(Model\Activity\Entity $activity, $vdotFactor, $vdotShape)
    {
        $this->Activity = $activity;
        $this->VdotFactor = $vdotFactor;
        $this->VdotShape = $vdotShape;
    }

    /**
     * @return string
     *
     * @TODO use Twig extension for LocalTime
     */
    public function getDate()
    {
        return LocalTime::date('d.m.Y', $this->Activity->timestamp());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Activity->comment();
    }

    /**
     * @return string
     */
    public function getDistance()
    {
        $distance = new Distance($this->Activity->distance());

        if ($this->Activity->isTrack()) {
            return $distance->stringMeter();
        }

        return $distance->string();
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->formatTime($this->Activity->duration());
    }

    /**
     * @return float
     */
    public function getVdotByTime()
    {
        return $this->Activity->vdotByTime();
    }

    /**
     * @return int [bpm]
     */
    public function getHeartRateInBpm()
    {
        return $this->Activity->hrAvg();
    }

    /**
     * @return float
     */
    public function getVdotByHeartRate()
    {
        return $this->Activity->vdotByHeartRate();
    }

    /**
     * @return string
     */
    public function getPrognosisByHeartRate()
    {
        return $this->prognosisFor($this->Activity->vdotByHeartRate(), $this->Activity->distance());
    }

    /**
     * @return float
     */
    public function getVdotByHeartRateAfterCorrection()
    {
        return $this->VdotFactor * $this->Activity->vdotByHeartRate();
    }

    /**
     * @return string
     */
    public function getPrognosisByHeartRateAfterCorrection()
    {
        return $this->prognosisFor($this->VdotFactor * $this->Activity->vdotByHeartRate(), $this->Activity->distance());
    }

    /**
     * @return float
     */
    public function getVdotByShape()
    {
        return $this->VdotShape;
    }

    /**
     * @return string
     */
    public function getPrognosisByShape()
    {
        return $this->prognosisFor($this->VdotShape, $this->Activity->distance());
    }

    /**
     * @return string
     */
    public function getShapeDeviation()
    {
        $prognosis = $this->prognosisInSecondsFor($this->VdotShape, $this->Activity->distance());
        $duration = $this->Activity->duration();
        $difference = 100 * ($prognosis - $duration) / $duration;

        return sprintf("%01.2f", $difference);
    }

    /**
     * @return float
     */
    public function getCorrectionFactor()
    {
        return (new VDOTCorrector())->fromActivity($this->Activity);
    }

    /**
     * @param float $vdot
     * @param float $distance [km]
     * @return float [s]
     */
    protected function prognosisInSecondsFor($vdot, $distance) {
        return (new Daniels($vdot))->getSeconds($distance);
    }

    /**
     * @param float $vdot
     * @param float $distance [km]
     * @return string
     */
    protected function prognosisFor($vdot, $distance) {
        return $this->formatTime($this->prognosisInSecondsFor($vdot, $distance));
    }

    /**
     * @param int $seconds [s]
     * @return string
     */
    protected function formatTime($seconds) {
        $Duration = new Duration($seconds);

        return $Duration->string(Duration::FORMAT_WITH_HOURS);
    }
}
