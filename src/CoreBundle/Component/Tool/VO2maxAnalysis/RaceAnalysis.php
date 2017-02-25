<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\VO2maxAnalysis;

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector;
use Runalyze\Model;
use Runalyze\Sports\Running\Prognosis\VO2max;
use Runalyze\Util\LocalTime;

class RaceAnalysis
{
    /** @var float */
    protected $VO2maxFactor;

    /** @var float */
    protected $VO2maxShape;

    /** @var Model\Activity\Entity */
    protected $Activity;

    /**
     * @param Model\Activity\Entity $activity
     * @param float $vo2maxFactor
     * @param float $vo2maxShape
     */
    public function __construct(Model\Activity\Entity $activity, $vo2maxFactor, $vo2maxShape)
    {
        $this->Activity = $activity;
        $this->VO2maxFactor = $vo2maxFactor;
        $this->VO2maxShape = $vo2maxShape;
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
     * @return float [ml/kg/min]
     */
    public function getVO2maxByTime()
    {
        return $this->Activity->vo2maxByTime();
    }

    /**
     * @return int [bpm]
     */
    public function getHeartRateInBpm()
    {
        return $this->Activity->hrAvg();
    }

    /**
     * @return float [ml/kg/min]
     */
    public function getVO2maxByHeartRate()
    {
        return $this->Activity->vo2maxByHeartRate();
    }

    /**
     * @return string
     */
    public function getPrognosisByHeartRate()
    {
        return $this->prognosisFor($this->Activity->vo2maxByHeartRate(), $this->Activity->distance());
    }

    /**
     * @return float [ml/kg/min]
     */
    public function getVO2maxByHeartRateAfterCorrection()
    {
        return $this->VO2maxFactor * $this->Activity->vo2maxByHeartRate();
    }

    /**
     * @return string
     */
    public function getPrognosisByHeartRateAfterCorrection()
    {
        return $this->prognosisFor($this->VO2maxFactor * $this->Activity->vo2maxByHeartRate(), $this->Activity->distance());
    }

    /**
     * @return float [ml/kg/min]
     */
    public function getVO2maxByShape()
    {
        return $this->VO2maxShape;
    }

    /**
     * @return string
     */
    public function getPrognosisByShape()
    {
        return $this->prognosisFor($this->VO2maxShape, $this->Activity->distance());
    }

    /**
     * @return string
     */
    public function getShapeDeviation()
    {
        $prognosis = $this->prognosisInSecondsFor($this->VO2maxShape, $this->Activity->distance());
        $duration = $this->Activity->duration();
        $difference = 100 * ($prognosis - $duration) / $duration;

        return sprintf("%01.2f", $difference);
    }

    /**
     * @return float
     */
    public function getCorrectionFactor()
    {
        return (new LegacyEffectiveVO2maxCorrector())->fromActivity($this->Activity);
    }

    /**
     * @param float $vo2max [ml/kg/min]
     * @param float $distance [km]
     * @return float [s]
     */
    protected function prognosisInSecondsFor($vo2max, $distance)
    {
        return (new VO2max($vo2max))->getSeconds($distance);
    }

    /**
     * @param float $vo2max [ml/kg/min]
     * @param float $distance [km]
     * @return string
     */
    protected function prognosisFor($vo2max, $distance)
    {
        return $this->formatTime($this->prognosisInSecondsFor($vo2max, $distance));
    }

    /**
     * @param int $seconds [s]
     * @return string
     */
    protected function formatTime($seconds)
    {
        $Duration = new Duration($seconds);

        return $Duration->string(Duration::FORMAT_WITH_HOURS);
    }
}
