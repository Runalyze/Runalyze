<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


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
     * @var integer
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
     * @var integer
     *
     * @ORM\Column(name="time", type="integer", precision=11, nullable=false)
     */
    private $time = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="timezone_offset", type="smallint", precision=6, nullable=true)
     */
    private $timezoneOffset;

    /**
     * @var integer
     *
     * @ORM\Column(name="created", type="integer", precision=11, nullable=true, options={"unsigned":true})
     */
    private $created = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="edited", type="integer", precision=11, nullable=true, options={"unsigned":true})
     */
    private $edited = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_public", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $isPublic = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_track", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $isTrack = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="distance", columnDefinition="decimal(6,2) unsigned DEFAULT NULL")
     */
    private $distance = null;

    /**
     * @var string
     *
     * @ORM\Column(name="s", columnDefinition="decimal(8,2) unsigned NOT NULL")
     */
    private $s = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="elapsed_time", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $elapsedTime = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="elevation", columnDefinition="smallint unsigned DEFAULT NULL")
     */
    private $elevation = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="kcal", columnDefinition="smallint unsigned DEFAULT NULL")
     */
    private $kcal = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="pulse_avg", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $pulseAvg = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="pulse_max", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $pulseMax = null;

    /**
     * @var string
     *
     * @ORM\Column(name="vdot", columnDefinition="decimal(5,2) unsigned DEFAULT NULL")
     */
    private $vdot = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="vdot_by_time", columnDefinition="decimal(5,2) unsigned DEFAULT NULL")
     */
    private $vdotByTime = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="vdot_with_elevation", columnDefinition="decimal(5,2) unsigned DEFAULT NULL" )
     */
    private $vdotWithElevation = '0.00';

    /**
     * @var boolean
     *
     * @ORM\Column(name="use_vdot", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 1")
     */
    private $useVdot = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="fit_vdot_estimate", columnDefinition="decimal(4,2) unsigned DEFAULT NULL")
     */
    private $fitVdotEstimate = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="fit_recovery_time", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $fitRecoveryTime = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="fit_hrv_analysis", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $fitHrvAnalysis = null;

    /**
     * @var string
     *
     * @ORM\Column(name="fit_training_effect", columnDefinition="decimal(2,1) unsigned DEFAULT NULL")
     */
    private $fitTrainingEffect = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fit_performance_condition", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $fitPerformanceCondition = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="jd_intensity", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $jdIntensity = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="rpe", columnDefinition="tinyint(2) unsigned DEFAULT NULL")
     */
    private $rpe = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="trimp", columnDefinition="smallint unsigned DEFAULT NULL")
     */
    private $trimp = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="cadence", type="integer", length=3, nullable=true, options={"unsigned":true})
     */
    private $cadence = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="power", type="integer", length=4, nullable=true, options={"unsigned":true})
     */
    private $power = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_strokes", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $totalStrokes = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="swolf", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $swolf = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="stride_length", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $strideLength = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="groundcontact", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $groundcontact = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="groundcontact_balance", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $groundcontactBalance = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="vertical_oscillation", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $verticalOscillation = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="vertical_ratio", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $verticalRatio = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="temperature", columnDefinition="tinyint(4) DEFAULT NULL")
     */
    private $temperature = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="wind_speed", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $windSpeed = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="wind_deg", columnDefinition="smallint(3) unsigned DEFAULT NULL")
     */
    private $windDeg;

    /**
     * @var boolean
     *
     * @ORM\Column(name="humidity", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $humidity;

    /**
     * @var integer
     *
     * @ORM\Column(name="pressure", columnDefinition="smallint(4) unsigned DEFAULT NULL")
     */
    private $pressure;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_night", columnDefinition="tinyint(1) unsigned DEFAULT NULL")
     */
    private $isNight;

    /**
     * @var integer
     *
     * @ORM\Column(name="weatherid", type="smallint", nullable=false, options={"unsigned":true, "default":1})
     */
    private $weatherid = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="weather_source", columnDefinition="tinyint(2) unsigned DEFAULT NULL")
     */
    private $weatherSource;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="text", length=65535, nullable=true)
     */
    private $routeName;

    /**
     * @var Route|null
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Route")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="routeid", referencedColumnName="id", nullable=true)
     * })
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(name="splits", type="text", length=16777215, nullable=true)
     */
    private $splits;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="partner", type="text", length=65535, nullable=true)
     */
    private $partner;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=65535, nullable=true)
     */
    private $notes;

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
    private $creator;

    /**
     * @var string
     *
     * @ORM\Column(name="creator_details", type="text", length=255, nullable=true)
     */
    private $creatorDetails;

    /**
     * @var integer
     *
     * @ORM\Column(name="activity_id", type="integer", nullable=true, options={"unsigned":true})
     */
    private $activityId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="lock", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $lock = '0';

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
     * Constructor
     */
    public function __construct()
    {
        $this->equipment = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tag = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sport
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport $sport
     *
     * @return Training
     */
    public function setSport(\Runalyze\Bundle\CoreBundle\Entity\Sport $sport = null)
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * Get sport
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Sport
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Set Type
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Type $defaultType
     *
     * @return Training
     */
    public function setType(\Runalyze\Bundle\CoreBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get Type
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set time
     *
     * @param integer $time
     *
     * @return Training
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set timezoneOffset
     *
     * @param integer $timezoneOffset
     *
     * @return Training
     */
    public function setTimezoneOffset($timezoneOffset)
    {
        $this->timezoneOffset = $timezoneOffset;

        return $this;
    }

    /**
     * Get timezoneOffset
     *
     * @return integer
     */
    public function getTimezoneOffset()
    {
        return $this->timezoneOffset;
    }

    /**
     * Set created
     *
     * @param integer $created
     *
     * @return Training
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return integer
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set edited
     *
     * @param integer $edited
     *
     * @return Training
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * Get edited
     *
     * @return integer
     */
    public function getEdited()
    {
        return $this->edited;
    }

    /**
     * @param bool $isPublic
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
     * Set distance
     *
     * @param float $distance
     *
     * @return Training
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set s
     *
     * @param float $s
     *
     * @return Training
     */
    public function setS($s)
    {
        $this->s = $s;

        return $this;
    }

    /**
     * Get s
     *
     * @return float
     */
    public function getS()
    {
        return $this->s;
    }

    /**
     * Set elapsedTime
     *
     * @param boolean $elapsedTime
     *
     * @return Training
     */
    public function setElapsedTime($elapsedTime)
    {
        $this->elapsedTime = $elapsedTime;

        return $this;
    }

    /**
     * Get elapsedTime
     *
     * @return integer
     */
    public function getElapsedTime()
    {
        return $this->elapsedTime;
    }

    /**
     * Set elevation
     *
     * @param integer $elevation
     *
     * @return Training
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * Get elevation
     *
     * @return integer
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Set kcal
     *
     * @param integer $kcal
     *
     * @return Training
     */
    public function setKcal($kcal)
    {
        $this->kcal = $kcal;

        return $this;
    }

    /**
     * Get kcal
     *
     * @return integer
     */
    public function getKcal()
    {
        return $this->kcal;
    }

    /**
     * Set pulseAvg
     *
     * @param integer $pulseAvg
     *
     * @return Training
     */
    public function setPulseAvg($pulseAvg)
    {
        $this->pulseAvg = $pulseAvg;

        return $this;
    }

    /**
     * Get pulseAvg
     *
     * @return integer
     */
    public function getPulseAvg()
    {
        return $this->pulseAvg;
    }

    /**
     * Set pulseMax
     *
     * @param integer $pulseMax
     *
     * @return Training
     */
    public function setPulseMax($pulseMax)
    {
        $this->pulseMax = $pulseMax;

        return $this;
    }

    /**
     * Get pulseMax
     *
     * @return integer
     */
    public function getPulseMax()
    {
        return $this->pulseMax;
    }

    /**
     * @param float $vdot
     *
     * @return $this
     */
    public function setVdot($vdot)
    {
        $this->vdot = $vdot;

        return $this;
    }

    /**
     * @return float
     */
    public function getVdot()
    {
        return $this->vdot;
    }

    /**
     * Set vdotByTime
     *
     * @param float $vdotByTime
     *
     * @return Training
     */
    public function setVdotByTime($vdotByTime)
    {
        $this->vdotByTime = $vdotByTime;

        return $this;
    }

    /**
     * Get vdotByTime
     *
     * @return float
     */
    public function getVdotByTime()
    {
        return $this->vdotByTime;
    }

    /**
     * Set vdotWithElevation
     *
     * @param float $vdotWithElevation
     *
     * @return Training
     */
    public function setVdotWithElevation($vdotWithElevation)
    {
        $this->vdotWithElevation = $vdotWithElevation;

        return $this;
    }

    /**
     * Get vdotWithElevation
     *
     * @return float
     */
    public function getVdotWithElevation()
    {
        return $this->vdotWithElevation;
    }

    /**
     * Set useVdot
     *
     * @param boolean $useVdot
     *
     * @return Training
     */
    public function setUseVdot($useVdot)
    {
        $this->useVdot = $useVdot;

        return $this;
    }

    /**
     * Get useVdot
     *
     * @return boolean
     */
    public function getUseVdot()
    {
        return $this->useVdot;
    }

    /**
     * Set fitVdotEstimate
     *
     * @param float $fitVdotEstimate
     *
     * @return Training
     */
    public function setFitVdotEstimate($fitVdotEstimate)
    {
        $this->fitVdotEstimate = $fitVdotEstimate;

        return $this;
    }

    /**
     * Get fitVdotEstimate
     *
     * @return float
     */
    public function getFitVdotEstimate()
    {
        return $this->fitVdotEstimate;
    }

    /**
     * Set fitRecoveryTime
     *
     * @param integer $fitRecoveryTime
     *
     * @return Training
     */
    public function setFitRecoveryTime($fitRecoveryTime)
    {
        $this->fitRecoveryTime = $fitRecoveryTime;

        return $this;
    }

    /**
     * Get fitRecoveryTime
     *
     * @return integer
     */
    public function getFitRecoveryTime()
    {
        return $this->fitRecoveryTime;
    }

    /**
     * Set fitHrvAnalysis
     *
     * @param integer $fitHrvAnalysis
     *
     * @return Training
     */
    public function setFitHrvAnalysis($fitHrvAnalysis)
    {
        $this->fitHrvAnalysis = $fitHrvAnalysis;

        return $this;
    }

    /**
     * Get fitHrvAnalysis
     *
     * @return integer
     */
    public function getFitHrvAnalysis()
    {
        return $this->fitHrvAnalysis;
    }

    /**
     * Set fitTrainingEffect
     *
     * @param float $fitTrainingEffect
     *
     * @return Training
     */
    public function setFitTrainingEffect($fitTrainingEffect)
    {
        $this->fitTrainingEffect = $fitTrainingEffect;

        return $this;
    }

    /**
     * Get fitTrainingEffect
     *
     * @return float
     */
    public function getFitTrainingEffect()
    {
        return $this->fitTrainingEffect;
    }

    /**
     * Set fitPerformanceCondition
     *
     * @param integer $fitPerformanceCondition
     *
     * @return Training
     */
    public function setFitPerformanceCondition($fitPerformanceCondition)
    {
        $this->fitPerformanceCondition = $fitPerformanceCondition;

        return $this;
    }

    /**
     * Get fitPerformanceCondition
     *
     * @return integer
     */
    public function getFitPerformanceCondition()
    {
        return $this->fitPerformanceCondition;
    }

    /**
     * Set jdIntensity
     *
     * @param integer $jdIntensity
     *
     * @return Training
     */
    public function setJdIntensity($jdIntensity)
    {
        $this->jdIntensity = $jdIntensity;

        return $this;
    }

    /**
     * Get jdIntensity
     *
     * @return integer
     */
    public function getJdIntensity()
    {
        return $this->jdIntensity;
    }

    /**
     * Set rpe
     *
     * @param integer $rpe
     *
     * @return Training
     */
    public function setRpe($rpe)
    {
        $this->rpe = $rpe;

        return $this;
    }

    /**
     * Get rpe
     *
     * @return integer
     */
    public function getRpe()
    {
        return $this->rpe;
    }

    /**
     * Set trimp
     *
     * @param integer $trimp
     *
     * @return Training
     */
    public function setTrimp($trimp)
    {
        $this->trimp = $trimp;

        return $this;
    }

    /**
     * Get trimp
     *
     * @return integer
     */
    public function getTrimp()
    {
        return $this->trimp;
    }

    /**
     * Set cadence
     *
     * @param integer $cadence
     *
     * @return Training
     */
    public function setCadence($cadence)
    {
        $this->cadence = $cadence;

        return $this;
    }

    /**
     * Get cadence
     *
     * @return integer
     */
    public function getCadence()
    {
        return $this->cadence;
    }

    /**
     * Set power
     *
     * @param integer $power
     *
     * @return Training
     */
    public function setPower($power)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * Get power
     *
     * @return integer
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * Set totalStrokes
     *
     * @param integer $totalStrokes
     *
     * @return Training
     */
    public function setTotalStrokes($totalStrokes)
    {
        $this->totalStrokes = $totalStrokes;

        return $this;
    }

    /**
     * Get totalStrokes
     *
     * @return integer
     */
    public function getTotalStrokes()
    {
        return $this->totalStrokes;
    }

    /**
     * Set swolf
     *
     * @param integer $swolf
     *
     * @return Training
     */
    public function setSwolf($swolf)
    {
        $this->swolf = $swolf;

        return $this;
    }

    /**
     * Get swolf
     *
     * @return integer
     */
    public function getSwolf()
    {
        return $this->swolf;
    }

    /**
     * Set strideLength
     *
     * @param integer $strideLength
     *
     * @return Training
     */
    public function setStrideLength($strideLength)
    {
        $this->strideLength = $strideLength;

        return $this;
    }

    /**
     * Get strideLength
     *
     * @return integer
     */
    public function getStrideLength()
    {
        return $this->strideLength;
    }

    /**
     * Set groundcontact
     *
     * @param integer $groundcontact
     *
     * @return Training
     */
    public function setGroundcontact($groundcontact)
    {
        $this->groundcontact = $groundcontact;

        return $this;
    }

    /**
     * Get groundcontact
     *
     * @return integer
     */
    public function getGroundcontact()
    {
        return $this->groundcontact;
    }

    /**
     * Set groundcontactBalance
     *
     * @param integer $groundcontactBalance
     *
     * @return Training
     */
    public function setGroundcontactBalance($groundcontactBalance)
    {
        $this->groundcontactBalance = $groundcontactBalance;

        return $this;
    }

    /**
     * Get groundcontactBalance
     *
     * @return integer
     */
    public function getGroundcontactBalance()
    {
        return $this->groundcontactBalance;
    }

    /**
     * Set verticalOscillation
     *
     * @param integer $verticalOscillation
     *
     * @return Training
     */
    public function setVerticalOscillation($verticalOscillation)
    {
        $this->verticalOscillation = $verticalOscillation;

        return $this;
    }

    /**
     * Get verticalOscillation
     *
     * @return integer
     */
    public function getVerticalOscillation()
    {
        return $this->verticalOscillation;
    }

    /**
     * Set verticalRatio
     *
     * @param integer $verticalRatio
     *
     * @return Training
     */
    public function setVerticalRatio($verticalRatio)
    {
        $this->verticalRatio = $verticalRatio;

        return $this;
    }

    /**
     * Get verticalRatio
     *
     * @return integer
     */
    public function getVerticalRatio()
    {
        return $this->verticalRatio;
    }

    /**
     * Set temperature
     *
     * @param integer $temperature
     *
     * @return Training
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Get temperature
     *
     * @return integer
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Set windSpeed
     *
     * @param integer $windSpeed
     *
     * @return Training
     */
    public function setWindSpeed($windSpeed)
    {
        $this->windSpeed = $windSpeed;

        return $this;
    }

    /**
     * Get windSpeed
     *
     * @return integer
     */
    public function getWindSpeed()
    {
        return $this->windSpeed;
    }

    /**
     * Set windDeg
     *
     * @param integer $windDeg
     *
     * @return Training
     */
    public function setWindDeg($windDeg)
    {
        $this->windDeg = $windDeg;

        return $this;
    }

    /**
     * Get windDeg
     *
     * @return integer
     */
    public function getWindDeg()
    {
        return $this->windDeg;
    }

    /**
     * Set humidity
     *
     * @param integer $humidity
     *
     * @return Training
     */
    public function setHumidity($humidity)
    {
        $this->humidity = $humidity;

        return $this;
    }

    /**
     * Get humidity
     *
     * @return integer
     */
    public function getHumidity()
    {
        return $this->humidity;
    }

    /**
     * Set pressure
     *
     * @param integer $pressure
     *
     * @return Training
     */
    public function setPressure($pressure)
    {
        $this->pressure = $pressure;

        return $this;
    }

    /**
     * Get pressure
     *
     * @return integer
     */
    public function getPressure()
    {
        return $this->pressure;
    }

    /**
     * @param bool $isNight
     * @return $this
     */
    public function setNight($isNight)
    {
        $this->isNight = $isNight;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNight()
    {
        return $this->isNight;
    }

    /**
     * Set weatherid
     *
     * @param integer $weatherid
     *
     * @return Training
     */
    public function setWeatherid($weatherid)
    {
        $this->weatherid = $weatherid;

        return $this;
    }

    /**
     * Get weatherid
     *
     * @return integer
     */
    public function getWeatherid()
    {
        return $this->weatherid;
    }

    /**
     * Set weatherSource
     *
     * @param integer $weatherSource
     *
     * @return Training
     */
    public function setWeatherSource($weatherSource)
    {
        $this->weatherSource = $weatherSource;

        return $this;
    }

    /**
     * Get weatherSource
     *
     * @return integer
     */
    public function getWeatherSource()
    {
        return $this->weatherSource;
    }

    /**
     * Set routeName
     *
     * @param string $routeName
     *
     * @return Training
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * Get routeName
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param \Runalyze\Bundle\CoreBundle\Entity\Route|null $route
     *
     * @return $this
     */
    public function setRoute(\Runalyze\Bundle\CoreBundle\Entity\Route $route = null)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return \Runalyze\Bundle\CoreBundle\Entity\Route|null
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set splits
     *
     * @param string $splits
     *
     * @return Training
     */
    public function setSplits($splits)
    {
        $this->splits = $splits;

        return $this;
    }

    /**
     * Get splits
     *
     * @return string
     */
    public function getSplits()
    {
        return $this->splits;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Training
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set partner
     *
     * @param string $partner
     *
     * @return Training
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Get partner
     *
     * @return string
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Training
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set account
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     *
     * @return Training
     */
    public function setAccount(\Runalyze\Bundle\CoreBundle\Entity\Account $account = null)
    {
        $this->account= $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set creator
     *
     * @param string $creator
     *
     * @return Training
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set creatorDetails
     *
     * @param string $creatorDetails
     *
     * @return Training
     */
    public function setCreatorDetails($creatorDetails)
    {
        $this->creatorDetails = $creatorDetails;

        return $this;
    }

    /**
     * Get creatorDetails
     *
     * @return string
     */
    public function getCreatorDetails()
    {
        return $this->creatorDetails;
    }

    /**
     * Set activityId
     *
     * @param integer $activityId
     *
     * @return Training
     */
    public function setActivityId($activityId)
    {
        $this->activityId = $activityId;

        return $this;
    }

    /**
     * Get activityId
     *
     * @return integer
     */
    public function getActivityId()
    {
        return $this->activityId;
    }

    /**
     * Set lock
     *
     * @param boolean $lock
     *
     * @return Training
     */
    public function setLock($lock)
    {
        $this->lock = $lock;

        return $this;
    }

    /**
     * Get lock
     *
     * @return boolean
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * Add equipment
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Equipment $equipment
     *
     * @return Training
     */
    public function addEquipment(\Runalyze\Bundle\CoreBundle\Entity\Equipment $equipment)
    {
        $this->equipment[] = $equipment;

        return $this;
    }

    /**
     * Remove equipment
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Equipment $equipment
     */
    public function removeEquipment(\Runalyze\Bundle\CoreBundle\Entity\Equipment $equipment)
    {
        $this->equipment->removeElement($equipment);
    }

    /**
     * Get equipment
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * Add tag
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Tag $tag
     *
     * @return Training
     */
    public function addTag(\Runalyze\Bundle\CoreBundle\Entity\Tag $tag)
    {
        $this->tag[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Tag $tag
     */
    public function removeTag(\Runalyze\Bundle\CoreBundle\Entity\Tag $tag)
    {
        $this->tag->removeElement($tag);
    }

    /**
     * Get tag
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTag()
    {
        return $this->tag;
    }

}
