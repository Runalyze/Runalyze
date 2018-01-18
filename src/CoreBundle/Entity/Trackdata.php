<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Entity\Adapter\TrackDataAdapter;
use Runalyze\Bundle\CoreBundle\Entity\Common\AccountRelatedEntityInterface;
use Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection;
use Runalyze\Model;

/**
 * Trackdata
 *
 * @ORM\Table(name="trackdata")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\TrackdataRepository")
 */
class Trackdata implements AccountRelatedEntityInterface
{
    /**
     * @var array|null [s]
     *
     * @ORM\Column(name="time", type="pipe_array", nullable=true)
     */
    private $time;

    /**
     * @var array|null [km]
     *
     * @ORM\Column(name="distance", type="pipe_array", nullable=true)
     */
    private $distance;

    /**
     * @var array|null [bpm]
     *
     * @ORM\Column(name="heartrate", type="pipe_array", nullable=true)
     */
    private $heartrate;

    /**
     * @var array|null [rpm]
     *
     * @ORM\Column(name="cadence", type="pipe_array", nullable=true)
     */
    private $cadence;

    /**
     * @var array|null [W]
     *
     * @ORM\Column(name="power", type="pipe_array", nullable=true)
     */
    private $power;

    /**
     * @var array|null [°C]
     *
     * @ORM\Column(name="temperature", type="pipe_array", nullable=true)
     */
    private $temperature;

    /**
     * @var array|null [ms]
     *
     * @ORM\Column(name="groundcontact", type="pipe_array", nullable=true)
     */
    private $groundcontact;

    /**
     * @var array|null [mm]
     *
     * @ORM\Column(name="vertical_oscillation", type="pipe_array", nullable=true)
     */
    private $verticalOscillation;

    /**
     * @var array|null [%*100]
     *
     * @ORM\Column(name="groundcontact_balance", type="pipe_array", nullable=true)
     */
    private $groundcontactBalance;

    /**
     * @var array|null [%]
     *
     * @ORM\Column(name="smo2_0", type="pipe_array", nullable=true)
     */
    private $smo20;

    /**
     * @var array|null [%]
     *
     * @ORM\Column(name="smo2_1", type="pipe_array", nullable=true)
     */
    private $smo21;

    /**
     * @var array|null [%]
     *
     * @ORM\Column(name="thb_0", type="pipe_array", nullable=true)
     */
    private $thb0;

    /**
     * @var array|null [%]
     *
     * @ORM\Column(name="thb_1", type="pipe_array", nullable=true)
     */
    private $thb1;

    /**
     * @var array|null [G]
     *
     * @ORM\Column(name="impact_gs_left", type="pipe_array", nullable=true)
     */
    private $impactGsLeft;

    /**
     * @var array|null [G]
     *
     * @ORM\Column(name="impact_gs_right", type="pipe_array", nullable=true)
     */
    private $impactGsRight;

    /**
     * @var array|null [G]
     *
     * @ORM\Column(name="braking_gs_left", type="pipe_array", nullable=true)
     */
    private $brakingGsLeft;

    /**
     * @var array|null [G]
     *
     * @ORM\Column(name="braking_gs_right", type="pipe_array", nullable=true)
     */
    private $brakingGsRight;

    /**
     * @var array|null [°]
     *
     * @ORM\Column(name="footstrike_type_left", type="pipe_array", nullable=true)
     */
    private $footstrikeTypeLeft;

    /**
     * @var array|null [°]
     *
     * @ORM\Column(name="footstrike_type_right", type="pipe_array", nullable=true)
     */
    private $footstrikeTypeRight;

    /**
     * @var array|null [°]
     *
     * @ORM\Column(name="pronation_excursion_left", type="pipe_array", nullable=true)
     */
    private $pronationExcursionLeft;

    /**
     * @var array|null [°]
     *
     * @ORM\Column(name="pronation_excursion_right", type="pipe_array", nullable=true)
     */
    private $pronationExcursionRight;

    /**
     * @var \Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection
     *
     * @ORM\Column(name="pauses", type="runalyze_pause_array", length=65535, nullable=true)
     */
    private $pauses;

    /**
     * @var Training
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Training", inversedBy="trackdata")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activityid", referencedColumnName="id", unique=true)
     * })
     */
    private $activity;

    /**
     * @var Account
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @var bool
     *
     * @ORM\Column(name="`lock`", type="boolean")
     */
    private $lock = false;

    /** @var TrackDataAdapter */
    private $Adapter;

    /** @var false|null|array [s/km] */
    private $pace = false;

    /** @var false|null|array [s/km] */
    private $gradeAdjustedPace = false;

    /** @var false|null|array (-100 .. 100) */
    private $gradient = false;

    /** @var false|null|array [cm] */
    private $strideLength = false;

    /** @var false|null|array [%] */
    private $verticalRatio = false;

    public function __construct()
    {
        $this->pauses = new PauseCollection();
    }

    /**
     * @param array|null $time [s]
     *
     * @return $this
     */
    public function setTime(array $time = null)
    {
        $this->time = $time;

        $this->pace = false;

        return $this;
    }

    /**
     * @return array|null [s]
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return bool
     */
    public function hasTime()
    {
        return null !== $this->time;
    }

    /**
     * @return int [s]
     */
    public function getTotalDuration()
    {
        if (!$this->hasTime()) {
            return 0;
        }

        return end($this->time);
    }

    /**
     * @param array|null $distance [km]
     *
     * @return $this
     */
    public function setDistance(array $distance = null)
    {
        $this->distance = $distance;

        $this->pace = false;
        $this->gradient = false;

        return $this;
    }

    /**
     * @return array|null [km]
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return bool
     */
    public function hasDistance()
    {
        return null !== $this->distance;
    }

    /**
     * @return float [km]
     */
    public function getTotalDistance()
    {
        if (!$this->hasDistance()) {
            return 0.0;
        }

        return end($this->distance);
    }

    /**
     * @param array|null $heartRate [bpm]
     *
     * @return $this
     */
    public function setHeartrate(array $heartRate = null)
    {
        $this->heartrate = $heartRate;

        return $this;
    }

    /**
     * @return array|null [bpm]
     */
    public function getHeartrate()
    {
        return $this->heartrate;
    }

    /**
     * @return bool
     */
    public function hasHeartrate()
    {
        return null !== $this->heartrate;
    }

    /**
     * @param array|null $cadence [rpm]
     *
     * @return $this
     */
    public function setCadence(array $cadence = null)
    {
        $this->cadence = $cadence;

        $this->strideLength = false;

        return $this;
    }

    /**
     * @return array|null [rpm]
     */
    public function getCadence()
    {
        return $this->cadence;
    }

    /**
     * @return bool
     */
    public function hasCadence()
    {
        return null !== $this->cadence;
    }

    /**
     * @param array|null $power [W]
     *
     * @return $this
     */
    public function setPower(array $power = null)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * @return array|null [W]
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * @return bool
     */
    public function hasPower()
    {
        return null !== $this->power;
    }

    /**
     * @param array|null $temperature [°C]
     *
     * @return $this
     */
    public function setTemperature(array $temperature = null)
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * @return array|null [°C]
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * @return bool
     */
    public function hasTemperature()
    {
        return null !== $this->temperature;
    }

    /**
     * @param array|null $groundcontact [ms]
     *
     * @return $this
     */
    public function setGroundcontact(array $groundcontact = null)
    {
        $this->groundcontact = $groundcontact;

        return $this;
    }

    /**
     * @return array|null [ms]
     */
    public function getGroundcontact()
    {
        return $this->groundcontact;
    }

    /**
     * @return bool
     */
    public function hasGroundcontact()
    {
        return null !== $this->groundcontact;
    }

    /**
     * @param array|null $verticalOscillation [mm]
     *
     * @return $this
     */
    public function setVerticalOscillation(array $verticalOscillation = null)
    {
        $this->verticalOscillation = $verticalOscillation;

        $this->verticalRatio = false;

        return $this;
    }

    /**
     * @return array|null [mm]
     */
    public function getVerticalOscillation()
    {
        return $this->verticalOscillation;
    }

    /**
     * @return bool
     */
    public function hasVerticalOscillation()
    {
        return null !== $this->verticalOscillation;
    }

    /**
     * @param array|null $groundcontactBalance [%*100]
     *
     * @return $this
     */
    public function setGroundcontactBalance(array $groundcontactBalance = null)
    {
        $this->groundcontactBalance = $groundcontactBalance;

        return $this;
    }

    /**
     * @return array|null [%*100]
     */
    public function getGroundcontactBalance()
    {
        return $this->groundcontactBalance;
    }

    /**
     * @return bool
     */
    public function hasGroundcontactBalance()
    {
        return null !== $this->groundcontactBalance;
    }

    /**
     * @param array|null $smo20 [%]
     *
     * @return $this
     */
    public function setSmo20(array $smo20 = null)
    {
        $this->smo20 = $smo20;

        return $this;
    }

    /**
     * @return array|null [%]
     */
    public function getSmo20()
    {
        return $this->smo20;
    }

    /**
     * @return bool
     */
    public function hasSmo20()
    {
        return null !== $this->smo20;
    }

    /**
     * @param array|null $smo21 [%]
     *
     * @return $this
     */
    public function setSmo21(array $smo21 = null)
    {
        $this->smo21 = $smo21;

        return $this;
    }

    /**
     * @return array|null [%]
     */
    public function getSmo21()
    {
        return $this->smo21;
    }

    /**
     * @return bool
     */
    public function hasSmo21()
    {
        return null !== $this->smo21;
    }

    /**
     * @param array|null $thb0 [%]
     *
     * @return $this
     */
    public function setThb0(array $thb0 = null)
    {
        $this->thb0 = $thb0;

        return $this;
    }

    /**
     * @return array|null [%]
     */
    public function getThb0()
    {
        return $this->thb0;
    }

    /**
     * @return bool
     */
    public function hasThb0()
    {
        return null !== $this->thb0;
    }

    /**
     * @param array|null $thb1 [%]
     *
     * @return $this
     */
    public function setThb1(array $thb1 = null)
    {
        $this->thb1 = $thb1;

        return $this;
    }

    /**
     * @return array|null [%]
     */
    public function getThb1()
    {
        return $this->thb1;
    }

    /**
     * @return bool
     */
    public function hasThb1()
    {
        return null !== $this->thb1;
    }

    /**
     * @param array|null $impactGsLeft [G]
     *
     * @return $this
     */
    public function setImpactGsLeft(array $impactGsLeft = null)
    {
        $this->impactGsLeft = $impactGsLeft;

        return $this;
    }

    /**
     * @return array|null [G]
     */
    public function getImpactGsLeft()
    {
        return $this->impactGsLeft;
    }

    /**
     * @return bool
     */
    public function hasImpactGsLeft()
    {
        return null !== $this->impactGsLeft;
    }

    /**
     * @param array|null $impactGsRight [G]
     *
     * @return $this
     */
    public function setImpactGsRight(array $impactGsRight = null)
    {
        $this->impactGsRight = $impactGsRight;

        return $this;
    }

    /**
     * @return array|null [G]
     */
    public function getImpactGsRight()
    {
        return $this->impactGsRight;
    }

    /**
     * @return bool
     */
    public function hasImpactGsRight()
    {
        return null !== $this->impactGsRight;
    }

    /**
     * @param array|null $brakingGsLeft [G]
     *
     * @return $this
     */
    public function setBrakingGsLeft(array $brakingGsLeft = null)
    {
        $this->brakingGsLeft = $brakingGsLeft;

        return $this;
    }

    /**
     * @return array|null [G]
     */
    public function getBrakingGsLeft()
    {
        return $this->brakingGsLeft;
    }

    /**
     * @return bool
     */
    public function hasBrakingGsLeft()
    {
        return null !== $this->brakingGsLeft;
    }

    /**
     * @param array|null $brakingGsRight [G]
     *
     * @return $this
     */
    public function setBrakingGsRight(array $brakingGsRight = null)
    {
        $this->brakingGsRight = $brakingGsRight;

        return $this;
    }

    /**
     * @return array|null [G]
     */
    public function getBrakingGsRight()
    {
        return $this->brakingGsRight;
    }

    /**
     * @return bool
     */
    public function hasBrakingGsRight()
    {
        return null !== $this->brakingGsRight;
    }

    /**
     * @param array|null $footstrikeTypeLeft [°]
     *
     * @return $this
     */
    public function setFootstrikeTypeLeft(array $footstrikeTypeLeft = null)
    {
        $this->footstrikeTypeLeft = $footstrikeTypeLeft;

        return $this;
    }

    /**
     * @return array|null [°]
     */
    public function getFootstrikeTypeLeft()
    {
        return $this->footstrikeTypeLeft;
    }

    /**
     * @return bool
     */
    public function hasFootstrikeTypeLeft()
    {
        return null !== $this->footstrikeTypeLeft;
    }

    /**
     * @param array|null $footstrikeTypeRight [°]
     *
     * @return $this
     */
    public function setFootstrikeTypeRight(array $footstrikeTypeRight = null)
    {
        $this->footstrikeTypeRight = $footstrikeTypeRight;

        return $this;
    }

    /**
     * @return array|null [°]
     */
    public function getFootstrikeTypeRight()
    {
        return $this->footstrikeTypeRight;
    }

    /**
     * @return bool
     */
    public function hasFootstrikeTypeRight()
    {
        return null !== $this->footstrikeTypeRight;
    }

    /**
     * @param array|null $pronationExcursionLeft [°]
     *
     * @return $this
     */
    public function setPronationExcursionLeft(array $pronationExcursionLeft = null)
    {
        $this->pronationExcursionLeft = $pronationExcursionLeft;

        return $this;
    }

    /**
     * @return array|null [°]
     */
    public function getPronationExcursionLeft()
    {
        return $this->pronationExcursionLeft;
    }

    /**
     * @return bool
     */
    public function hasPronationExcursionLeft()
    {
        return null !== $this->pronationExcursionLeft;
    }

    /**
     * @param array|null $pronationExcursionRight [°]
     *
     * @return $this
     */
    public function setPronationExcursionRight(array $pronationExcursionRight = null)
    {
        $this->pronationExcursionRight = $pronationExcursionRight;

        return $this;
    }

    /**
     * @return array|null [°]
     */
    public function getPronationExcursionRight()
    {
        return $this->pronationExcursionRight;
    }

    /**
     * @return bool
     */
    public function hasPronationExcursionRight()
    {
        return null !== $this->pronationExcursionRight;
    }

    /**
     * @param \Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection $pauses
     *
     * @return $this
     */
    public function setPauses(PauseCollection $pauses)
    {
        $this->pauses = $pauses;

        return $this;
    }

    /**
     * @return \Runalyze\Parser\Activity\Common\Data\Pause\PauseCollection
     */
    public function getPauses()
    {
        return $this->pauses;
    }

    /**
     * @param Training $activity
     *
     * @return $this
     */
    public function setActivity(Training $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return Training
     */
    public function getActivity()
    {
        return $this->activity;
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
     * @param bool $flag
     * @return $this
     */
    public function setLock($flag)
    {
        $this->lock = (bool)$flag;

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
     * @return bool
     */
    public function isLocked()
    {
        return $this->lock;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (
            (null === $this->time && empty($this->time)) &&
            (null === $this->distance && empty($this->distance)) &&
            (null === $this->heartrate && empty($this->heartrate)) &&
            (null === $this->cadence && empty($this->cadence)) &&
            (null === $this->power && empty($this->power)) &&
            (null === $this->temperature && empty($this->temperature)) &&
            (null === $this->groundcontact && empty($this->groundcontact)) &&
            (null === $this->groundcontactBalance && empty($this->groundcontactBalance)) &&
            (null === $this->verticalOscillation && empty($this->verticalOscillation)) &&
            (null === $this->smo20 && empty($this->smo20)) &&
            (null === $this->smo21 && empty($this->smo21)) &&
            (null === $this->thb0 && empty($this->thb0)) &&
            (null === $this->thb1 && empty($this->thb1)) &&
            (null === $this->impactGsLeft && empty($this->impactGsLeft)) &&
            (null === $this->impactGsRight && empty($this->impactGsRight)) &&
            (null === $this->brakingGsLeft && empty($this->brakingGsLeft)) &&
            (null === $this->brakingGsRight && empty($this->brakingGsRight)) &&
            (null === $this->footstrikeTypeLeft && empty($this->footstrikeTypeLeft)) &&
            (null === $this->footstrikeTypeRight && empty($this->footstrikeTypeRight)) &&
            (null === $this->pronationExcursionLeft && empty($this->pronationExcursionLeft)) &&
            (null === $this->pronationExcursionRight && empty($this->pronationExcursionRight)) &&
            $this->pauses->isEmpty()
        );
    }

    /**
     * @return Model\Trackdata\Entity
     */
    public function getLegacyModel()
    {
        return new Model\Trackdata\Entity([
            Model\Trackdata\Entity::ACTIVITYID => $this->activity->getId(),
            Model\Trackdata\Entity::TIME => $this->time,
            Model\Trackdata\Entity::DISTANCE => $this->distance,
            Model\Trackdata\Entity::HEARTRATE => $this->heartrate,
            Model\Trackdata\Entity::CADENCE => $this->cadence,
            Model\Trackdata\Entity::POWER => $this->power,
            Model\Trackdata\Entity::TEMPERATURE => $this->temperature,
            Model\Trackdata\Entity::GROUNDCONTACT => $this->groundcontact,
            Model\Trackdata\Entity::GROUNDCONTACT_BALANCE => $this->groundcontactBalance,
            Model\Trackdata\Entity::VERTICAL_OSCILLATION => $this->verticalOscillation,
            Model\Trackdata\Entity::SMO2_0 => $this->smo20,
            Model\Trackdata\Entity::SMO2_1 => $this->smo21,
            Model\Trackdata\Entity::THB_0 => $this->thb0,
            Model\Trackdata\Entity::THB_1 => $this->thb1,
            Model\Trackdata\Entity::IMPACT_GS_LEFT => $this->impactGsLeft,
            Model\Trackdata\Entity::IMPACT_GS_RIGHT => $this->impactGsRight,
            Model\Trackdata\Entity::BRAKING_GS_LEFT => $this->brakingGsLeft,
            Model\Trackdata\Entity::BRAKING_GS_RIGHT => $this->brakingGsRight,
            Model\Trackdata\Entity::FOOTSTRIKE_TYPE_LEFT => $this->footstrikeTypeLeft,
            Model\Trackdata\Entity::FOOTSTRIKE_TYPE_RIGHT => $this->footstrikeTypeRight,
            Model\Trackdata\Entity::PRONATION_EXCURSION_LEFT => $this->pronationExcursionLeft,
            Model\Trackdata\Entity::PRONATION_EXCURSION_RIGHT => $this->pronationExcursionRight,

            // Legacy model does still use the pauses object
            //Model\Trackdata\Entity::PAUSES => $this->pauses
        ]);
    }

    /**
     * @return TrackDataAdapter
     */
    public function getAdapter()
    {
        if (null === $this->Adapter) {
            $this->Adapter = new TrackDataAdapter($this);
        }

        return $this->Adapter;
    }

    /**
     * @return array|null [s/km]
     */
    public function getPace()
    {
        if (false === $this->pace) {
            $this->getAdapter()->calculatePace();
        }

        return $this->pace;
    }

    /**
     * @param array|null $pace [s/km]
     */
    public function setPace(array $pace = null)
    {
        $this->pace = $pace;
    }

    /**
     * @return bool
     */
    public function hasPace()
    {
        return null !== $this->time && null !== $this->distance;
    }

    /**
     * @return array|null [s/km]
     */
    public function getGradeAdjustedPace()
    {
        if (false === $this->gradeAdjustedPace || false === $this->pace || false === $this->gradient) {
            $this->getAdapter()->calculateGradeAdjustedPace();
        }

        return $this->gradeAdjustedPace;
    }

    /**
     * @param array|null $gradeAdjustedPace [s/km]
     */
    public function setGradeAdjustedPace(array $gradeAdjustedPace = null)
    {
        $this->gradeAdjustedPace = $gradeAdjustedPace;
    }

    /**
     * @return array|null (-100 .. 100)
     */
    public function getGradient()
    {
        if (false === $this->gradient) {
            $this->getAdapter()->calculateGradient();
        }

        return $this->gradient;
    }

    /**
     * @param array|null $gradient (-100 .. 100)
     */
    public function setGradient(array $gradient = null)
    {
        $this->gradient = $gradient;
    }

    /**
     * @return array|null [cm]
     */
    public function getStrideLength()
    {
        if (false === $this->strideLength || false === $this->pace) {
            $this->getAdapter()->calculateStrideLength();
        }

        return $this->strideLength;
    }

    /**
     * @param array|null $strideLength [cm]
     */
    public function setStrideLength(array $strideLength = null)
    {
        $this->strideLength = $strideLength;
    }

    /**
     * @return bool
     */
    public function hasStrideLength()
    {
        return null !== $this->cadence && $this->hasPace();
    }

    /**
     * @return array|null [%]
     */
    public function getVerticalRatio()
    {
        if (false === $this->verticalRatio || false === $this->strideLength) {
            $this->getAdapter()->calculateVerticalRatio();
        }

        return $this->verticalRatio;
    }

    /**
     * @param array|null $verticalRatio [%]
     */
    public function setVerticalRatio(array $verticalRatio = null)
    {
        $this->verticalRatio = $verticalRatio;
    }

    /**
     * @return bool
     */
    public function hasVerticalRatio()
    {
        return null !== $this->verticalOscillation && $this->hasStrideLength();
    }
}
