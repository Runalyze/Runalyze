<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Training
 *
 * @ORM\Table(name="training", indexes={@ORM\Index(name="time", columns={"accountid", "time"}), @ORM\Index(name="sportid", columns={"accountid", "sportid"}), @ORM\Index(name="typeid", columns={"accountid", "typeid"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\TrainingRepository")
 */
class Training
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", precision=10, unique=true, nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Sport
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Sport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sportid", referencedColumnName="id", nullable=false)
     * })
     */
    private $sport;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Type
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Type")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeid", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var int [timestamp]
     *
     * @ORM\Column(name="time", type="integer", precision=11, nullable=false)
     */
    private $time = 0;

    /**
     * @var int|null [min]
     *
     * @ORM\Column(name="timezone_offset", type="smallint", precision=6, nullable=true)
     */
    private $timezoneOffset = null;

    /**
     * @var int|null [timestamp]
     *
     * @ORM\Column(name="created", type="integer", precision=11, nullable=true, options={"unsigned":true})
     */
    private $created = null;

    /**
     * @var int|null [timestamp]
     *
     * @ORM\Column(name="edited", type="integer", precision=11, nullable=true, options={"unsigned":true})
     */
    private $edited = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $isPublic = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_track", type="boolean", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $isTrack = false;

    /**
     * @var float|null [km]
     *
     * @ORM\Column(name="distance", columnDefinition="decimal(6,2) unsigned DEFAULT NULL")
     */
    private $distance = null;

    /**
     * @var float|null [s]
     *
     * @ORM\Column(name="s", columnDefinition="decimal(8,2) unsigned NOT NULL")
     */
    private $s = 0.0;

    /**
     * @var int|null [s]
     *
     * @ORM\Column(name="elapsed_time", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $elapsedTime = null;

    /**
     * @var int|null [m]
     *
     * @ORM\Column(name="elevation", columnDefinition="smallint unsigned DEFAULT NULL")
     */
    private $elevation = null;

    /**
     * @var int|null [kcal]
     *
     * @ORM\Column(name="kcal", columnDefinition="smallint unsigned DEFAULT NULL")
     */
    private $kcal = null;

    /**
     * @var int|null [bpm]
     *
     * @ORM\Column(name="pulse_avg", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $pulseAvg = null;

    /**
     * @var int|null [bpm]
     *
     * @ORM\Column(name="pulse_max", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $pulseMax = null;

    /**
     * @var float|null [ml/kg/min]
     *
     * @ORM\Column(name="vo2max", columnDefinition="decimal(5,2) unsigned DEFAULT NULL")
     */
    private $vo2max = null;

    /**
     * @var float|null [ml/kg/min]
     *
     * @ORM\Column(name="vo2max_by_time", columnDefinition="decimal(5,2) unsigned DEFAULT NULL")
     */
    private $vo2maxByTime = null;

    /**
     * @var float|null [ml/kg/min]
     *
     * @ORM\Column(name="vo2max_with_elevation", columnDefinition="decimal(5,2) unsigned DEFAULT NULL" )
     */
    private $vo2maxWithElevation = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_vo2max", type="boolean", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 1")
     */
    private $useVO2max = true;

    /**
     * @var float|null [ml/kg/min]
     *
     * @ORM\Column(name="fit_vo2max_estimate", columnDefinition="decimal(4,2) unsigned DEFAULT NULL")
     */
    private $fitVO2maxEstimate = null;

    /**
     * @var int|null [min]
     *
     * @ORM\Column(name="fit_recovery_time", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $fitRecoveryTime = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="fit_hrv_analysis", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $fitHrvAnalysis = null;

    /**
     * @var float|null
     *
     * @ORM\Column(name="fit_training_effect", columnDefinition="decimal(2,1) unsigned DEFAULT NULL")
     */
    private $fitTrainingEffect = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="fit_performance_condition", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $fitPerformanceCondition = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="rpe", columnDefinition="tinyint(2) unsigned DEFAULT NULL")
     */
    private $rpe = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="trimp", columnDefinition="smallint unsigned DEFAULT NULL")
     */
    private $trimp = null;

    /**
     * @var int|null [rpm]
     *
     * @ORM\Column(name="cadence", type="integer", length=3, nullable=true, options={"unsigned":true})
     */
    private $cadence = null;

    /**
     * @var int|null [W]
     *
     * @ORM\Column(name="power", type="integer", length=4, nullable=true, options={"unsigned":true})
     */
    private $power = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="total_strokes", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $totalStrokes = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="swolf", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $swolf = null;

    /**
     * @var bool|null [cm]
     *
     * @ORM\Column(name="stride_length", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $strideLength = null;

    /**
     * @var int|null [ms]
     *
     * @ORM\Column(name="groundcontact", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $groundcontact = null;

    /**
     * @var int|null [%ooL]
     *
     * @ORM\Column(name="groundcontact_balance", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $groundcontactBalance = null;

    /**
     * @var int|null [mm]
     *
     * @ORM\Column(name="vertical_oscillation", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $verticalOscillation = null;

    /**
     * @var int|null [%]
     *
     * @ORM\Column(name="vertical_ratio", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $verticalRatio = null;

    /**
     * @var int|null [°C]
     *
     * @ORM\Column(name="temperature", columnDefinition="tinyint(4) DEFAULT NULL")
     */
    private $temperature = null;

    /**
     * @var bool|null [km/h]
     *
     * @ORM\Column(name="wind_speed", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $windSpeed = null;

    /**
     * @var int|null [°]
     *
     * @ORM\Column(name="wind_deg", columnDefinition="smallint(3) unsigned DEFAULT NULL")
     */
    private $windDeg = null;

    /**
     * @var bool|null [%]
     *
     * @ORM\Column(name="humidity", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $humidity = null;

    /**
     * @var int [hPa]
     *
     * @ORM\Column(name="pressure", columnDefinition="smallint(4) unsigned DEFAULT NULL")
     */
    private $pressure = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_night", type="boolean", columnDefinition="tinyint(1) unsigned DEFAULT NULL")
     */
    private $isNight = null;

    /**
     * @var int
     *
     * @ORM\Column(name="weatherid", type="smallint", nullable=false, options={"unsigned":true, "default":1})
     */
    private $weatherid = 1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="weather_source", columnDefinition="tinyint(2) unsigned DEFAULT NULL")
     */
    private $weatherSource = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="route", type="text", length=65535, nullable=true)
     */
    private $routeName = null;

    /**
     * @var Route|null
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Route")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="routeid", referencedColumnName="id", nullable=true)
     * })
     */
    private $route = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="splits", type="text", length=16777215, nullable=true)
     */
    private $splits = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=true)
     */
    private $comment = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="partner", type="text", length=65535, nullable=true)
     */
    private $partner = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="notes", type="text", length=65535, nullable=true)
     */
    private $notes = null;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @var string
     *
     * @ORM\Column(name="creator", type="string", length=100, nullable=false, options={"default":""})
     */
    private $creator = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="creator_details", type="text", length=255, nullable=true)
     */
    private $creatorDetails = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="activity_id", type="integer", nullable=true, options={"unsigned":true})
     */
    private $activityId = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="`lock`", type="boolean", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $lock = false;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Equipment", inversedBy="activity")
     * @ORM\JoinTable(name="activity_equipment",
     *   joinColumns={
     *     @ORM\JoinColumn(name="activityid", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="equipmentid", referencedColumnName="id")
     *   }
     * )
     */
    private $equipment;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="activity")
     * @ORM\JoinTable(name="activity_tag",
     *   joinColumns={
     *     @ORM\JoinColumn(name="activityid", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="tagid", referencedColumnName="id")
     *   }
     * )
     */
    private $tag;

    public function __construct()
    {
        $this->equipment = new ArrayCollection();
        $this->tag = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Sport $sport
     *
     * @return $this
     */
    public function setSport(Sport $sport)
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * @return Sport
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * @param null|Type $type
     *
     * @return $this
     */
    public function setType(Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return null|Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $time [timestamp]
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return int [timestamp]
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param null|int $timezoneOffset [min]
     *
     * @return $this
     */
    public function setTimezoneOffset($timezoneOffset)
    {
        $this->timezoneOffset = $timezoneOffset;

        return $this;
    }

    /**
     * @return null|int [min]
     */
    public function getTimezoneOffset()
    {
        return $this->timezoneOffset;
    }

    /**
     * @param null|int $created [timestamp]
     *
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return null|int [timestamp]
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param null|int $edited [timestamp]
     *
     * @return $this
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * @return null|int [timestamp]
     */
    public function getEdited()
    {
        return $this->edited;
    }

    /**
     * @param bool $isPublic
     *
     * @return $this
     */
    public function setPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isTrack
     *
     * @return $this
     */
    public function setTrack($isTrack)
    {
        $this->isTrack = $isTrack;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTrack()
    {
        return $this->isTrack;
    }

    /**
     * @param null|float $distance [km]
     *
     * @return $this
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return null|float [km]
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param float $s [s]
     *
     * @return $this
     */
    public function setS($s)
    {
        $this->s = $s;

        return $this;
    }

    /**
     * @return float [s]
     */
    public function getS()
    {
        return $this->s;
    }

    /**
     * @param null|bool $elapsedTime [s]
     *
     * @return $this
     */
    public function setElapsedTime($elapsedTime)
    {
        $this->elapsedTime = $elapsedTime;

        return $this;
    }

    /**
     * @return null|int [s]
     */
    public function getElapsedTime()
    {
        return $this->elapsedTime;
    }

    /**
     * @param null|int $elevation [m]
     *
     * @return $this
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * @return null|int [m]
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * @param null|int $kcal [kcal]
     *
     * @return $this
     */
    public function setKcal($kcal)
    {
        $this->kcal = $kcal;

        return $this;
    }

    /**
     * @return null|int [kcal]
     */
    public function getKcal()
    {
        return $this->kcal;
    }

    /**
     * @param null|int $pulseAvg [bpm]
     *
     * @return $this
     */
    public function setPulseAvg($pulseAvg)
    {
        $this->pulseAvg = $pulseAvg;

        return $this;
    }

    /**
     * @return null|int [bpm]
     */
    public function getPulseAvg()
    {
        return $this->pulseAvg;
    }

    /**
     * @param null|int $pulseMax [bpm]
     *
     * @return $this
     */
    public function setPulseMax($pulseMax)
    {
        $this->pulseMax = $pulseMax;

        return $this;
    }

    /**
     * @return null|int [bpm]
     */
    public function getPulseMax()
    {
        return $this->pulseMax;
    }

    /**
     * @param null|float $vo2max [ml/kg/min]
     *
     * @return $this
     */
    public function setVO2max($vo2max)
    {
        $this->vo2max = $vo2max;

        return $this;
    }

    /**
     * @return null|float [ml/kg/min]
     */
    public function getVO2max()
    {
        return $this->vo2max;
    }

    /**
     * @param null|float $vo2maxByTime [ml/kg/min]
     *
     * @return $this
     */
    public function setVO2maxByTime($vo2maxByTime)
    {
        $this->vo2maxByTime = $vo2maxByTime;

        return $this;
    }

    /**
     * @return null|float [ml/kg/min]
     */
    public function getVO2maxByTime()
    {
        return $this->vo2maxByTime;
    }

    /**
     * @param null|float $vo2maxWithElevation [ml/kg/min]
     *
     * @return $this
     */
    public function setVO2maxWithElevation($vo2maxWithElevation)
    {
        $this->vo2maxWithElevation = $vo2maxWithElevation;

        return $this;
    }

    /**
     * @return null|float [ml/kg/min]
     */
    public function getVO2maxWithElevation()
    {
        return $this->vo2maxWithElevation;
    }

    /**
     * @param bool $useVO2max
     *
     * @return $this
     */
    public function setUseVO2max($useVO2max)
    {
        $this->useVO2max = $useVO2max;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseVO2max()
    {
        return $this->useVO2max;
    }

    /**
     * @param null|float $fitVO2maxEstimate [ml/kg/min]
     *
     * @return $this
     */
    public function setFitVO2maxEstimate($fitVO2maxEstimate)
    {
        $this->fitVO2maxEstimate = $fitVO2maxEstimate;

        return $this;
    }

    /**
     * @return null|float [ml/kg/min]
     */
    public function getFitVO2maxEstimate()
    {
        return $this->fitVO2maxEstimate;
    }

    /**
     * @param null|int $fitRecoveryTime [min]
     *
     * @return $this
     */
    public function setFitRecoveryTime($fitRecoveryTime)
    {
        $this->fitRecoveryTime = $fitRecoveryTime;

        return $this;
    }

    /**
     * @return null|int [min]
     */
    public function getFitRecoveryTime()
    {
        return $this->fitRecoveryTime;
    }

    /**
     * @param null|int $fitHrvAnalysis
     *
     * @return $this
     */
    public function setFitHrvAnalysis($fitHrvAnalysis)
    {
        $this->fitHrvAnalysis = $fitHrvAnalysis;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getFitHrvAnalysis()
    {
        return $this->fitHrvAnalysis;
    }

    /**
     * @param null|float $fitTrainingEffect
     *
     * @return $this
     */
    public function setFitTrainingEffect($fitTrainingEffect)
    {
        $this->fitTrainingEffect = $fitTrainingEffect;

        return $this;
    }

    /**
     * @return null|float
     */
    public function getFitTrainingEffect()
    {
        return $this->fitTrainingEffect;
    }

    /**
     * @param null|int $fitPerformanceCondition
     *
     * @return $this
     */
    public function setFitPerformanceCondition($fitPerformanceCondition)
    {
        $this->fitPerformanceCondition = $fitPerformanceCondition;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getFitPerformanceCondition()
    {
        return $this->fitPerformanceCondition;
    }

    /**
     * @param null|int $rpe
     *
     * @return $this
     */
    public function setRpe($rpe)
    {
        $this->rpe = $rpe;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getRpe()
    {
        return $this->rpe;
    }

    /**
     * @param null|int $trimp
     *
     * @return $this
     */
    public function setTrimp($trimp)
    {
        $this->trimp = $trimp;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getTrimp()
    {
        return $this->trimp;
    }

    /**
     * @param null|int $cadence [rpm]
     *
     * @return $this
     */
    public function setCadence($cadence)
    {
        $this->cadence = $cadence;

        return $this;
    }

    /**
     * @return null|int [rpm]
     */
    public function getCadence()
    {
        return $this->cadence;
    }

    /**
     * @param null|int $power [W]
     *
     * @return $this
     */
    public function setPower($power)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * @return null|int [W]
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * @param null|int $totalStrokes
     *
     * @return $this
     */
    public function setTotalStrokes($totalStrokes)
    {
        $this->totalStrokes = $totalStrokes;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getTotalStrokes()
    {
        return $this->totalStrokes;
    }

    /**
     * @param null|int $swolf
     *
     * @return $this
     */
    public function setSwolf($swolf)
    {
        $this->swolf = $swolf;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getSwolf()
    {
        return $this->swolf;
    }

    /**
     * @param null|int $strideLength [cm]
     *
     * @return $this
     */
    public function setStrideLength($strideLength)
    {
        $this->strideLength = $strideLength;

        return $this;
    }

    /**
     * @return null|int [cm]
     */
    public function getStrideLength()
    {
        return $this->strideLength;
    }

    /**
     * @param null|int $groundcontact [ms]
     *
     * @return $this
     */
    public function setGroundcontact($groundcontact)
    {
        $this->groundcontact = $groundcontact;

        return $this;
    }

    /**
     * @return null|int [ms]
     */
    public function getGroundcontact()
    {
        return $this->groundcontact;
    }

    /**
     * @param null|int $groundcontactBalance [%ooL]
     *
     * @return $this
     */
    public function setGroundcontactBalance($groundcontactBalance)
    {
        $this->groundcontactBalance = $groundcontactBalance;

        return $this;
    }

    /**
     * @return null|int [%ooL]
     */
    public function getGroundcontactBalance()
    {
        return $this->groundcontactBalance;
    }

    /**
     * @param null|int $verticalOscillation [mm]
     *
     * @return $this
     */
    public function setVerticalOscillation($verticalOscillation)
    {
        $this->verticalOscillation = $verticalOscillation;

        return $this;
    }

    /**
     * @return null|int [mm]
     */
    public function getVerticalOscillation()
    {
        return $this->verticalOscillation;
    }

    /**
     * @param null|int $verticalRatio [%]
     *
     * @return $this
     */
    public function setVerticalRatio($verticalRatio)
    {
        $this->verticalRatio = $verticalRatio;

        return $this;
    }

    /**
     * @return null|int [%]
     */
    public function getVerticalRatio()
    {
        return $this->verticalRatio;
    }

    /**
     * @param null|int $temperature [°C]
     *
     * @return $this
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * @return null|int [°C]
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * @param null|int $windSpeed [km/h]
     *
     * @return $this
     */
    public function setWindSpeed($windSpeed)
    {
        $this->windSpeed = $windSpeed;

        return $this;
    }

    /**
     * @return null|int [km/h]
     */
    public function getWindSpeed()
    {
        return $this->windSpeed;
    }

    /**
     * @param null|int $windDeg [°]
     *
     * @return $this
     */
    public function setWindDeg($windDeg)
    {
        $this->windDeg = $windDeg;

        return $this;
    }

    /**
     * @return null|int [°]
     */
    public function getWindDeg()
    {
        return $this->windDeg;
    }

    /**
     * @param null|int $humidity [%]
     *
     * @return $this
     */
    public function setHumidity($humidity)
    {
        $this->humidity = $humidity;

        return $this;
    }

    /**
     * @return null|int [%]
     */
    public function getHumidity()
    {
        return $this->humidity;
    }

    /**
     * @param null|int $pressure [hPa]
     *
     * @return $this
     */
    public function setPressure($pressure)
    {
        $this->pressure = $pressure;

        return $this;
    }

    /**
     * @return null|int [hPa]
     */
    public function getPressure()
    {
        return $this->pressure;
    }

    /**
     * @param null|bool $isNight
     *
     * @return $this
     */
    public function setNight($isNight)
    {
        $this->isNight = $isNight;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function isNight()
    {
        return $this->isNight;
    }

    /**
     * @param int $weatherid
     *
     * @return $this
     */
    public function setWeatherid($weatherid)
    {
        $this->weatherid = $weatherid;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeatherid()
    {
        return $this->weatherid;
    }

    /**
     * @param null|int $weatherSource
     *
     * @return $this
     */
    public function setWeatherSource($weatherSource)
    {
        $this->weatherSource = $weatherSource;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getWeatherSource()
    {
        return $this->weatherSource;
    }

    /**
     * @param null|string $routeName
     *
     * @return $this
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param null|Route $route
     *
     * @return $this
     */
    public function setRoute(Route $route = null)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return null|Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param null|string $splits
     *
     * @return $this
     */
    public function setSplits($splits)
    {
        $this->splits = $splits;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSplits()
    {
        return $this->splits;
    }

    /**
     * @param null|string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param null|string $partner
     *
     * @return $this
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param null|string $notes
     *
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param string $creator
     *
     * @return $this
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param null|string $creatorDetails
     *
     * @return $this
     */
    public function setCreatorDetails($creatorDetails)
    {
        $this->creatorDetails = $creatorDetails;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCreatorDetails()
    {
        return $this->creatorDetails;
    }

    /**
     * @param null|int $activityId
     *
     * @return $this
     */
    public function setActivityId($activityId)
    {
        $this->activityId = $activityId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getActivityId()
    {
        return $this->activityId;
    }

    /**
     * @param bool $lock
     *
     * @return $this
     */
    public function setLock($lock)
    {
        $this->lock = $lock;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * @param Equipment $equipment
     *
     * @return $this
     */
    public function addEquipment(Equipment $equipment)
    {
        $this->equipment[] = $equipment;

        return $this;
    }

    /**
     * @param Equipment $equipment
     */
    public function removeEquipment(Equipment $equipment)
    {
        $this->equipment->removeElement($equipment);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @param Tag $tag
     *
     * @return $this
     */
    public function addTag(Tag $tag)
    {
        $this->tag[] = $tag;

        return $this;
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tag->removeElement($tag);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTag()
    {
        return $this->tag;
    }
}
