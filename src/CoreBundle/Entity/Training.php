<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Entity\Adapter\ActivityAdapter;
use Runalyze\Bundle\CoreBundle\Entity\Common\AccountRelatedEntityInterface;
use Runalyze\Bundle\CoreBundle\Entity\Common\IdentifiableEntityInterface;
use Runalyze\Parser\Activity\Common\Data\Round\RoundCollection;
use Runalyze\Util\LocalTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Training
 *
 * @ORM\Table(name="training", indexes={@ORM\Index(name="time", columns={"accountid", "time"}), @ORM\Index(name="sportid", columns={"accountid", "sportid"}), @ORM\Index(name="typeid", columns={"accountid", "typeid"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\TrainingRepository")
 * @ORM\EntityListeners({"Runalyze\Bundle\CoreBundle\EntityListener\ActivityListener"})
 * @ORM\HasLifecycleCallbacks()
 */
class Training implements IdentifiableEntityInterface, AccountRelatedEntityInterface
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
     * @Assert\NotBlank(message = "You need to choose a sport.")
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Sport", inversedBy = "trainings")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sportid", referencedColumnName="id", nullable=false)
     * })
     */
    private $sport;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Type
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Type", inversedBy = "trainings")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeid", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var int [timestamp]
     * @Assert\NotBlank(message = "Every activity needs a time")

     * @ORM\Column(name="time", type="integer", nullable=false)
     */
    private $time;

    /**
     * @var int|null [min]
     *
     * @ORM\Column(name="timezone_offset", type="smallint", nullable=true)
     */
    private $timezoneOffset = null;

    /**
     * @var int|null [timestamp]
     *
     * @ORM\Column(name="created", type="integer", nullable=true, options={"unsigned":true})
     */
    private $created = null;

    /**
     * @var int|null [timestamp]
     *
     * @ORM\Column(name="edited", type="integer", nullable=true, options={"unsigned":true})
     */
    private $edited = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean")
     */
    private $isPublic = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_track", type="boolean")
     */
    private $isTrack = false;

    /**
     * @var float|null [km]
     *
     * @ORM\Column(name="distance", type="casted_decimal_2", precision=6, scale=2, nullable=true, options={"unsigned":true})
     */
    private $distance = null;

    /**
     * @var float [s]
     *
     * @Assert\GreaterThan(0)
     * @ORM\Column(name="s", type="casted_decimal_2", precision=8, scale=2, options={"unsigned":true})
     */
    private $s;

    /**
     * @var int|null [s]
     *
     * @ORM\Column(name="elapsed_time", type="integer", nullable=true, options={"unsigned":true})
     */
    private $elapsedTime = null;

    /**
     * @var int|null [m]
     *
     * @ORM\Column(name="elevation", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $elevation = null;

    /**
     * @var float|null [0.0 .. 10.0]
     *
     * @ORM\Column(name="climb_score", type="casted_decimal_1", precision=3, scale=1, nullable=true, options={"unsigned":true})
     */
    private $climbScore = null;

    /**
     * @var float|null [0.00 .. 1.00]
     *
     * @ORM\Column(name="percentage_hilly", type="casted_decimal_2", precision=3, scale=2, nullable=true, options={"unsigned":true})
     */
    private $percentageHilly = null;

    /**
     * @var int|null [kcal]
     *
     * @ORM\Column(name="kcal", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $kcal = null;

    /**
     * @var int|null [bpm]
     *
     * @Assert\Range(
     *      min = 30,
     *      max = 255,
     *      minMessage = "Your average heartrate must be at least {{ limit }} bpm",
     *      maxMessage = "Your average heartrate cannot be greater than {{ limit }} bpm"
     * )
     *
     * @ORM\Column(name="pulse_avg", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $pulseAvg = null;

    /**
     * @var int|null [bpm]
     * @Assert\Range(
     *      min = 30,
     *      max = 255,
     *      minMessage = "Your maximum heartrate must be at least {{ limit }} bpm",
     *      maxMessage = "Your maximum heartrate cannot be greater than {{ limit }} bpm"
     * )
     * @ORM\Column(name="pulse_max", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $pulseMax = null;

    /**
     * @var float|null [ml/kg/min]
     *
     * @ORM\Column(name="vo2max", type="casted_decimal_2", precision=5, scale=2, nullable=true, options={"unsigned":true})
     */
    private $vo2max = null;

    /**
     * @var float|null [ml/kg/min]
     *
     * @ORM\Column(name="vo2max_by_time", type="casted_decimal_2", precision=5, scale=2, nullable=true, options={"unsigned":true})
     */
    private $vo2maxByTime = null;

    /**
     * @var float|null [ml/kg/min]
     *
     * @ORM\Column(name="vo2max_with_elevation", type="casted_decimal_2", precision=5, scale=2, nullable=true, options={"unsigned":true})
     */
    private $vo2maxWithElevation = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_vo2max", type="boolean")
     */
    private $useVO2max = true;

    /**
     * @var float|null [ml/kg/min]
     *
     * @ORM\Column(name="fit_vo2max_estimate", type="casted_decimal_2", precision=4, scale=2, nullable=true, options={"unsigned":true})
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
     * @var float|null [1.0 .. 5.0]
     *
     * @ORM\Column(name="fit_training_effect", type="casted_decimal_1", precision=2, scale=1, nullable=true, options={"unsigned":true})
     */
    private $fitTrainingEffect = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="fit_performance_condition", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $fitPerformanceCondition = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="fit_performance_condition_end", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $fitPerformanceConditionEnd = null;

    /**
     * @var int|null [6 .. 20]
     *
     * @ORM\Column(name="rpe", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $rpe = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="trimp", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $trimp = null;

    /**
     * @var int|null [rpm]
     *
     * @ORM\Column(name="cadence", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $cadence = null;

    /**
     * @var int|null [W]
     *
     * @ORM\Column(name="power", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $power = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_power_calculated", type="boolean", nullable=true, options={"default":null})
     */
    private $isPowerCalculated = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="total_strokes", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $totalStrokes = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="swolf", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $swolf = null;

    /**
     * @var bool|null [cm]
     *
     * @ORM\Column(name="stride_length", type="tinyint", nullable=true, options={"unsigned":true})
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
     * @ORM\Column(name="vertical_oscillation", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $verticalOscillation = null;

    /**
     * @var int|null [%]
     *
     * @ORM\Column(name="vertical_ratio", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $verticalRatio = null;

    /**
     * @var float|null [G]
     *
     * @ORM\Column(name="avg_impact_gs_left", type="float", nullable=true, options={"unsigned":true}, columnDefinition="FLOAT")
     */
    private $avgImpactGsLeft = null;

    /**
     * @var float|null [G]
     *
     * @ORM\Column(name="avg_impact_gs_right", type="float", nullable=true, options={"unsigned":true}, columnDefinition="FLOAT")
     */
    private $avgImpactGsRight = null;

    /**
     * @var float|null [G]
     *
     * @ORM\Column(name="avg_braking_gs_left", type="float", nullable=true, options={"unsigned":true}, columnDefinition="FLOAT")
     */
    private $avgBrakingGsLeft = null;

    /**
     * @var float|null [G]
     *
     * @ORM\Column(name="avg_braking_gs_right", type="float", nullable=true, options={"unsigned":true}, columnDefinition="FLOAT")
     */
    private $avgBrakingGsRight = null;

    /**
     * @var int|null [°]
     *
     * @ORM\Column(name="avg_footstrike_type_left", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $avgFootstrikeTypeLeft = null;

    /**
     * @var int|null [°]
     *
     * @ORM\Column(name="avg_footstrike_type_right", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $avgFootstrikeTypeRight = null;

    /**
     * @var float|null [°]
     *
     * @ORM\Column(name="avg_pronation_excursion_left", type="float", nullable=true, columnDefinition="FLOAT")
     */
    private $avgPronationExcursionLeft = null;

    /**
     * @var float|null [°]
     *
     * @ORM\Column(name="avg_pronation_excursion_right", type="float", nullable=true, columnDefinition="FLOAT")
     */
    private $avgPronationExcursionRight = null;

    /**
     * @var int|null [°C]
     *
     * @ORM\Column(name="temperature", type="tinyint", nullable=true)
     */
    private $temperature = null;

    /**
     * @var int|null [km/h]
     *
     * @ORM\Column(name="wind_speed", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $windSpeed = null;

    /**
     * @var int|null [°]
     * @Assert\Range(
     *      min = 0,
     *      max = 359,
     *      minMessage = "The wind direction cannot be less than {{ limit }}",
     *      maxMessage = "The wind direction cannot be greater than {{ limit }}"
     * )
     * @ORM\Column(name="wind_deg", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $windDeg = null;

    /**
     * @var int|null [%]
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "The humidity cannot be less than {{ limit }}",
     *      maxMessage = "The humidity cannot be greater than {{ limit }}. Even if it feels like this."
     * )
     * @ORM\Column(name="humidity", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $humidity = null;

    /**
     * @var int|null [hPa]
     *
     * @ORM\Column(name="pressure", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $pressure = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_night", type="boolean", nullable=true)
     */
    private $isNight = null;

    /**
     * @var int enum, see \Runalyze\Profile\Weather\WeatherConditionProfile
     *
     * @ORM\Column(name="weatherid", type="smallint", nullable=false, options={"unsigned":true, "default":1})
     */
    private $weatherid = 1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="weather_source", type="tinyint", nullable=true, options={"unsigned":true})
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
     * @var \Runalyze\Parser\Activity\Common\Data\Round\RoundCollection
     *
     * @ORM\Column(name="splits", type="runalyze_round_array", length=16777215, nullable=true)
     */
    private $splits;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="text", length=65535, nullable=true)
     */
    private $title = null;

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
     * @ORM\Column(name="`lock`", type="boolean")
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

    /**
     * @var Trackdata|null
     *
     * @ORM\OneToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Trackdata", mappedBy="activity")
     */
    private $trackdata;

    /**
     * @var Swimdata|null
     *
     * @ORM\OneToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Swimdata", mappedBy="activity")
     */
    private $swimdata;

    /**
     * @var Hrv|null
     *
     * @ORM\OneToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Hrv", mappedBy="activity")
     */
    private $hrv;

    /**
     * @var Raceresult|null
     *
     * @ORM\OneToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Raceresult", mappedBy="activity")
     */
    private $raceresult;

    /** @var null|ActivityAdapter */
    private $Adapter;

    public function __construct()
    {
        $this->equipment = new ArrayCollection();
        $this->tag = new ArrayCollection();
        $this->splits = new RoundCollection();
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
     * @return LocalTime
     */
    public function getDateTime()
    {
        return new LocalTime($this->time);
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
     * @ORM\PrePersist
     */
    public function setCreatedToNow()
    {
        $this->setCreated(time());
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
     * @ORM\PreUpdate
     */
    public function setEditedToNow()
    {
        $this->setEdited(time());
    }

    /**
     * @param bool $isPublic
     *
     * @return $this
     */
    public function setPublic($isPublic)
    {
        $this->isPublic = (bool)$isPublic;

        return $this;
    }

    /**
     * @param bool $isPublic
     *
     * @return $this
     */
    public function setIsPublic($isPublic)
    {
        return $this->setPublic($isPublic);
    }

    /**
     * @return $this
     */
    public function togglePrivacy()
    {
        $this->isPublic = !$this->isPublic;

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
        $this->isTrack = (bool)$isTrack;

        return $this;
    }

    /**
     * @param bool $isTrack
     *
     * @return $this
     */
    public function setIsTrack($isTrack)
    {
        return $this->setTrack($isTrack);
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
        $this->s = (double)$s;

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
     * @param null|int $elapsedTime [s]
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
        $this->elevation = null === $elevation ? null : (int)$elevation;

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
     * @param null|float $score [0.0 .. 10.0]
     *
     * @return $this
     */
    public function setClimbScore($score)
    {
        $this->climbScore = $score;

        return $this;
    }

    /**
     * @return null|float [0.0 .. 10.0]
     */
    public function getClimbScore()
    {
        return $this->climbScore;
    }

    /**
     * @param null|float $percentage [0.00 .. 1.00]
     *
     * @return $this
     */
    public function setPercentageHilly($percentage)
    {
        $this->percentageHilly = $percentage;

        return $this;
    }

    /**
     * @return null|float [0.00 .. 1.00]
     */
    public function getPercentageHilly()
    {
        return $this->percentageHilly;
    }

    /**
     * @return null|float [0.00 .. 1.00]
     */
    public function getPercentageFlat()
    {
        return null !== $this->percentageHilly ? 1.0 - $this->percentageHilly : null;
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
     * @param null|int $fitPerformanceConditionEnd
     *
     * @return $this
     */
    public function setFitPerformanceConditionEnd($fitPerformanceConditionEnd)
    {
        $this->fitPerformanceConditionEnd = $fitPerformanceConditionEnd;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getFitPerformanceConditionEnd()
    {
        return $this->fitPerformanceConditionEnd;
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
     * @param bool|null $flag
     * @return $this
     */
    public function setPowerCalculated($flag)
    {
        $this->isPowerCalculated = null === $flag ? null : (bool)$flag;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isPowerCalculated()
    {
        return $this->isPowerCalculated;
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
     * @param null|float $avgImpactGsLeft [G]
     *
     * @return $this
     */
    public function setAvgImpactGsLeft($avgImpactGsLeft)
    {
        $this->avgImpactGsLeft = $avgImpactGsLeft;

        return $this;
    }

    /**
     * @return null|float [G]
     */
    public function getAvgImpactGsLeft()
    {
        return $this->avgImpactGsLeft;
    }

    /**
     * @param null|float $avgImpactGsRight [G]
     *
     * @return $this
     */
    public function setAvgImpactGsRight($avgImpactGsRight)
    {
        $this->avgImpactGsRight = $avgImpactGsRight;

        return $this;
    }

    /**
     * @return null|float [G]
     */
    public function getAvgImpactGsRight()
    {
        return $this->avgImpactGsRight;
    }

    /**
     * @param null|float $avgBrakingGsLeft [G]
     *
     * @return $this
     */
    public function setAvgBrakingGsLeft($avgBrakingGsLeft)
    {
        $this->avgBrakingGsLeft = $avgBrakingGsLeft;

        return $this;
    }

    /**
     * @return null|float [G]
     */
    public function getAvgBrakingGsLeft()
    {
        return $this->avgBrakingGsLeft;
    }

    /**
     * @param null|float $avgBrakingGsRight [G]
     *
     * @return $this
     */
    public function setAvgBrakingGsRight($avgBrakingGsRight)
    {
        $this->avgBrakingGsRight = $avgBrakingGsRight;

        return $this;
    }

    /**
     * @return null|float [G]
     */
    public function getAvgBrakingGsRight()
    {
        return $this->avgBrakingGsRight;
    }

    /**
     * @param null|int $avgFootstrikeTypeLeft [°]
     *
     * @return $this
     */
    public function setAvgFootstrikeTypeLeft($avgFootstrikeTypeLeft)
    {
        $this->avgFootstrikeTypeLeft = $avgFootstrikeTypeLeft;

        return $this;
    }

    /**
     * @return null|int [°]
     */
    public function getAvgFootstrikeTypeLeft()
    {
        return $this->avgFootstrikeTypeLeft;
    }

    /**
     * @param null|int $avgFootstrikeTypeRight [°]
     *
     * @return $this
     */
    public function setAvgFootstrikeTypeRight($avgFootstrikeTypeRight)
    {
        $this->avgFootstrikeTypeRight = $avgFootstrikeTypeRight;

        return $this;
    }

    /**
     * @return null|int [°]
     */
    public function getAvgFootstrikeTypeRight()
    {
        return $this->avgFootstrikeTypeRight;
    }

    /**
     * @param null|float $avgPronationExcursionLeft [°]
     *
     * @return $this
     */
    public function setAvgPronationExcursionLeft($avgPronationExcursionLeft)
    {
        $this->avgPronationExcursionLeft = $avgPronationExcursionLeft;

        return $this;
    }

    /**
     * @return null|float [°]
     */
    public function getAvgPronationExcursionLeft()
    {
        return $this->avgPronationExcursionLeft;
    }

    /**
     * @param null|float $avgPronationExcursionRight [°]
     *
     * @return $this
     */
    public function setAvgPronationExcursionRight($avgPronationExcursionRight)
    {
        $this->avgPronationExcursionRight = $avgPronationExcursionRight;

        return $this;
    }

    /**
     * @return null|float [°]
     */
    public function getAvgPronationExcursionRight()
    {
        return $this->avgPronationExcursionRight;
    }

    /**
     * @param null|int $temperature [°C]
     *
     * @return $this
     */
    public function setTemperature($temperature)
    {
        $this->temperature = null === $temperature ? null : (int)$temperature;

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
     * @param int $weatherid enum, see \Runalyze\Profile\Weather\WeatherConditionProfile
     *
     * @return $this
     */
    public function setWeatherid($weatherid)
    {
        $this->weatherid = $weatherid;

        return $this;
    }

    /**
     * @return int enum, see \Runalyze\Profile\Weather\WeatherConditionProfile
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
        $this->weatherSource = null === $weatherSource ? null : (int)$weatherSource;

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

        if ($this->hasRoute()) {
            $this->getRoute()->setName($routeName);
        }

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

        if (null !== $route && null !== $this->account) {
            $route->setAccount($this->account);
        }

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
     * @return bool
     */
    public function hasRoute()
    {
        return null !== $this->route;
    }

    /**
     * @param RoundCollection $splits
     *
     * @return $this
     */
    public function setSplits(RoundCollection $splits)
    {
        $this->splits = $splits;

        return $this;
    }

    public function setSplitsToClone()
    {
        $this->splits = clone $this->splits;
    }

    /**
     * @return RoundCollection
     */
    public function getSplits()
    {
        return $this->splits;
    }

    /**
     * @param null|string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
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
        $this->activityId = null === $activityId ? null : (int)$activityId;

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

    public function setTrackdata(Trackdata $trackData = null)
    {
        $this->trackdata = $trackData;

        if (null !== $trackData) {
            $trackData->setActivity($this);

            if (null !== $this->account) {
                $trackData->setAccount($this->account);
            }
        }
    }

    /**
     * @return Trackdata|null
     */
    public function getTrackdata()
    {
        return $this->trackdata;
    }

    /**
     * @return bool
     */
    public function hasTrackdata()
    {
        return null !== $this->trackdata;
    }

    public function setSwimdata(Swimdata $swimData = null)
    {
        $this->swimdata = $swimData;

        if (null !== $swimData) {
            $swimData->setActivity($this);

            if (null !== $this->account) {
                $swimData->setAccount($this->account);
            }
        }
    }

    /**
     * @return Swimdata|null
     */
    public function getSwimdata()
    {
        return $this->swimdata;
    }

    /**
     * @return bool
     */
    public function hasSwimdata()
    {
        return null !== $this->swimdata;
    }

    public function setHrv(Hrv $hrv = null)
    {
        $this->hrv = $hrv;

        if (null !== $hrv) {
            $hrv->setActivity($this);

            if (null !== $this->account) {
                $hrv->setAccount($this->account);
            }
        }
    }

    /**
     * @return Hrv|null
     */
    public function getHrv()
    {
        return $this->hrv;
    }

    /**
     * @return bool
     */
    public function hasHrv()
    {
        return null !== $this->hrv;
    }

    public function setRaceresult(Raceresult $raceResult = null)
    {
        $this->raceresult = $raceResult;

        if (null !== $raceResult) {
            $raceResult->setActivity($this);

            if (null !== $this->account) {
                $raceResult->setAccount($this->account);
            }
        }
    }

    /**
     * @return Raceresult|null
     */
    public function getRaceresult()
    {
        return $this->raceresult;
    }

    /**
     * @return bool
     */
    public function hasRaceresult()
    {
        return null !== $this->raceresult;
    }

    /**
     * @return ActivityAdapter
     */
    public function getAdapter()
    {
        if (null === $this->Adapter) {
            $this->Adapter = new ActivityAdapter($this);
        }

        return $this->Adapter;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateSimpleCalculatedValues()
    {
        if (0.0 == $this->distance) {
            $this->distance = null;
        }

        $this->getAdapter()->updateSimpleCalculatedValues();
    }

    /**
     * @ORM\PrePersist()
     */
    public function useElevationFromRouteIfEmpty()
    {
        if (null === $this->elevation || 0 == $this->elevation) {
            $this->getAdapter()->useElevationFromRoute();
        }
    }
}
