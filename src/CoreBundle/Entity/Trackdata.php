<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Model;

/**
 * Trackdata
 *
 * @ORM\Table(name="trackdata", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\TrackdataRepository")
 */
class Trackdata
{
    /**
     * @var string
     *
     * @ORM\Column(name="time", type="text", nullable=true)
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(name="distance", type="text", nullable=true)
     */
    private $distance;

    /**
     * @var string
     *
     * @ORM\Column(name="heartrate", type="text", nullable=true)
     */
    private $heartrate;

    /**
     * @var string
     *
     * @ORM\Column(name="cadence", type="text", nullable=true)
     */
    private $cadence;

    /**
     * @var string
     *
     * @ORM\Column(name="power", type="text", nullable=true)
     */
    private $power;

    /**
     * @var string
     *
     * @ORM\Column(name="temperature", type="text", nullable=true)
     */
    private $temperature;

    /**
     * @var string
     *
     * @ORM\Column(name="groundcontact", type="text", nullable=true)
     */
    private $groundcontact;

    /**
     * @var string
     *
     * @ORM\Column(name="vertical_oscillation", type="text", nullable=true)
     */
    private $verticalOscillation;

    /**
     * @var string
     *
     * @ORM\Column(name="groundcontact_balance", type="text", nullable=true)
     */
    private $groundcontactBalance;

    /**
     * @var string
     *
     * @ORM\Column(name="smo2_0", type="text", nullable=true)
     */
    private $smo20;

    /**
     * @var string
     *
     * @ORM\Column(name="smo2_1", type="text", nullable=true)
     */
    private $smo21;

    /**
     * @var string
     *
     * @ORM\Column(name="thb_0", type="text", nullable=true)
     */
    private $thb0;

    /**
     * @var string
     *
     * @ORM\Column(name="thb_1", type="text", nullable=true)
     */
    private $thb1;

    /**
     * @var string
     *
     * @ORM\Column(name="pauses", type="text", length=65535, nullable=true)
     */
    private $pauses;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Training
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Training")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activityid", referencedColumnName="id", unique=true)
     * })
     */
    private $activityid;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\Column(name="accountid", type="integer", precision=10, nullable=false, options={"unsigned":true})
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $accountid;



    /**
     * Set time
     *
     * @param string $time
     *
     * @return Trackdata
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set distance
     *
     * @param string $distance
     *
     * @return Trackdata
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return string
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set heartrate
     *
     * @param string $heartrate
     *
     * @return Trackdata
     */
    public function setHeartrate($heartrate)
    {
        $this->heartrate = $heartrate;

        return $this;
    }

    /**
     * Get heartrate
     *
     * @return string
     */
    public function getHeartrate()
    {
        return $this->heartrate;
    }

    /**
     * Set cadence
     *
     * @param string $cadence
     *
     * @return Trackdata
     */
    public function setCadence($cadence)
    {
        $this->cadence = $cadence;

        return $this;
    }

    /**
     * Get cadence
     *
     * @return string
     */
    public function getCadence()
    {
        return $this->cadence;
    }

    /**
     * Set power
     *
     * @param string $power
     *
     * @return Trackdata
     */
    public function setPower($power)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * Get power
     *
     * @return string
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * Set temperature
     *
     * @param string $temperature
     *
     * @return Trackdata
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Get temperature
     *
     * @return string
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Set groundcontact
     *
     * @param string $groundcontact
     *
     * @return Trackdata
     */
    public function setGroundcontact($groundcontact)
    {
        $this->groundcontact = $groundcontact;

        return $this;
    }

    /**
     * Get groundcontact
     *
     * @return string
     */
    public function getGroundcontact()
    {
        return $this->groundcontact;
    }

    /**
     * Set verticalOscillation
     *
     * @param string $verticalOscillation
     *
     * @return Trackdata
     */
    public function setVerticalOscillation($verticalOscillation)
    {
        $this->verticalOscillation = $verticalOscillation;

        return $this;
    }

    /**
     * Get verticalOscillation
     *
     * @return string
     */
    public function getVerticalOscillation()
    {
        return $this->verticalOscillation;
    }

    /**
     * Set groundcontactBalance
     *
     * @param string $groundcontactBalance
     *
     * @return Trackdata
     */
    public function setGroundcontactBalance($groundcontactBalance)
    {
        $this->groundcontactBalance = $groundcontactBalance;

        return $this;
    }

    /**
     * Get groundcontactBalance
     *
     * @return string
     */
    public function getGroundcontactBalance()
    {
        return $this->groundcontactBalance;
    }

    /**
     * Set smo20
     *
     * @param string $smo20
     *
     * @return Trackdata
     */
    public function setSmo20($smo20)
    {
        $this->smo20 = $smo20;

        return $this;
    }

    /**
     * Get smo20
     *
     * @return string
     */
    public function getSmo20()
    {
        return $this->smo20;
    }

    /**
     * Set smo21
     *
     * @param string $smo21
     *
     * @return Trackdata
     */
    public function setSmo21($smo21)
    {
        $this->smo21 = $smo21;

        return $this;
    }

    /**
     * Get smo21
     *
     * @return string
     */
    public function getSmo21()
    {
        return $this->smo21;
    }

    /**
     * Set thb0
     *
     * @param string $thb0
     *
     * @return Trackdata
     */
    public function setThb0($thb0)
    {
        $this->thb0 = $thb0;

        return $this;
    }

    /**
     * Get thb0
     *
     * @return string
     */
    public function getThb0()
    {
        return $this->thb0;
    }

    /**
     * Set thb1
     *
     * @param string $thb1
     *
     * @return Trackdata
     */
    public function setThb1($thb1)
    {
        $this->thb1 = $thb1;

        return $this;
    }

    /**
     * Get thb1
     *
     * @return string
     */
    public function getThb1()
    {
        return $this->thb1;
    }

    /**
     * Set pauses
     *
     * @param string $pauses
     *
     * @return Trackdata
     */
    public function setPauses($pauses)
    {
        $this->pauses = $pauses;

        return $this;
    }

    /**
     * Get pauses
     *
     * @return string
     */
    public function getPauses()
    {
        return $this->pauses;
    }

    /**
     * Set activityid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Training $activityid
     *
     * @return Trackdata
     */
    public function setActivityid(\Runalyze\Bundle\CoreBundle\Entity\Training $activityid = null)
    {
        $this->activityid = $activityid;

        return $this;
    }

    /**
     * Get activityid
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Training
     */
    public function getActivityid()
    {
        return $this->activityid;
    }

    /**
     * Set accountid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $accountid
     *
     * @return Trackdata
     */
    public function setAccountid(\Runalyze\Bundle\CoreBundle\Entity\Account $accountid = null)
    {
        $this->accountid = $accountid;

        return $this;
    }

    /**
     * Get accountid
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Account
     */
    public function getAccountid()
    {
        return $this->accountid;
    }

    /**
     * @return Model\Trackdata\Entity
     */
    public function getLegacyModel()
    {
        // TODO: activityid, accountid (entities have no getId() so far)
        return new Model\Trackdata\Entity([
            //Model\Trackdata\Entity::ACTIVITYID => $this->activityid->getId(),
            //Model\Trackdata\Entity::ACCOUNTID => $this->accountid->getId(),
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
            Model\Trackdata\Entity::PAUSES => $this->pauses
        ]);
    }
}
