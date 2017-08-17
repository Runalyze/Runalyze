<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Pause\PauseCollection;
use Runalyze\Model;

/**
 * Trackdata
 *
 * @ORM\Table(name="trackdata")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\TrackdataRepository")
 */
class Trackdata
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
     * @var PauseCollection
     *
     * @ORM\Column(name="pauses", type="runalyze_pause_array", length=65535, nullable=true)
     */
    private $pauses;

    /**
     * @var Training
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Training", inversedBy="trackdata")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activityid", referencedColumnName="id", unique=true)
     * })
     */
    private $activity;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @param array|null $time [s]
     *
     * @return $this
     */
    public function setTime(array $time = null)
    {
        $this->time = $time;

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
     * @param array|null $distance [km]
     *
     * @return $this
     */
    public function setDistance(array $distance = null)
    {
        $this->distance = $distance;

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
     * @param PauseCollection $pauses
     *
     * @return $this
     */
    public function setPauses(PauseCollection $pauses)
    {
        $this->pauses = $pauses;

        return $this;
    }

    /**
     * @return PauseCollection
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
            // Legacy model does still use the pauses object
            //Model\Trackdata\Entity::PAUSES => $this->pauses
        ]);
    }
}
