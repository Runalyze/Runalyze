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
     * @var boolean
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
    private $route;

    /**
     * @var integer
     *
     * @ORM\Column(name="routeid", type="integer", nullable=false, options={"unsigned":true, "default":0})
     */
    private $routeid = '0';

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
     * @var \Account
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false, onDelete="cascade")
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

}

