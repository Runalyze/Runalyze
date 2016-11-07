<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user", indexes={@ORM\Index(name="time", columns={"accountid", "time"}), @ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class User
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="time", type="integer", precision=11, nullable=false, options={"unsigned":true})
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="decimal", precision=5, scale=2, nullable=true, options={"unsigned":true})
     */
    private $weight;

    /**
     * @var integer
     *
     * @ORM\Column(name="pulse_rest", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $pulseRest;

    /**
     * @var integer
     *
     * @ORM\Column(name="pulse_max", columnDefinition="tinyint(3) unsigned DEFAULT NULL")
     */
    private $pulseMax;

    /**
     * @var string
     *
     * @ORM\Column(name="fat", type="decimal", precision=3, scale=1, nullable=true)
     */
    private $fat;

    /**
     * @var string
     *
     * @ORM\Column(name="water", type="decimal", precision=3, scale=1, nullable=true)
     */
    private $water;

    /**
     * @var string
     *
     * @ORM\Column(name="muscles", type="decimal", precision=3, scale=1, nullable=true)
     */
    private $muscles;

    /**
     * @var integer
     *
     * @ORM\Column(name="sleep_duration", type="smallint", precision=3, nullable=true, options={"unsigned":true})
     */
    private $sleepDuration;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set time
     *
     * @param string $time
     *
     * @return User
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
     * Set weight
     *
     * @param string $weight
     *
     * @return User
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set pulseRest
     *
     * @param string $pulseRest
     *
     * @return User
     */
    public function setPulseRest($pulseRest)
    {
        $this->pulseRest = $pulseRest;

        return $this;
    }

    /**
     * Get pulseRest
     *
     * @return string
     */
    public function getPulseRest()
    {
        return $this->pulseRest;
    }

    /**
     * Set pulseMax
     *
     * @param string $pulseMax
     *
     * @return User
     */
    public function setPulseMax($pulseMax)
    {
        $this->pulseMax = $pulseMax;

        return $this;
    }

    /**
     * Get pulseMax
     *
     * @return string
     */
    public function getPulseMax()
    {
        return $this->pulseMax;
    }

    /**
     * Set fat
     *
     * @param string $fat
     *
     * @return User
     */
    public function setFat($fat)
    {
        $this->fat = $fat;

        return $this;
    }

    /**
     * Get fat
     *
     * @return string
     */
    public function getFat()
    {
        return $this->fat;
    }

    /**
     * Set water
     *
     * @param string $water
     *
     * @return User
     */
    public function setWater($water)
    {
        $this->water = $water;

        return $this;
    }

    /**
     * Get water
     *
     * @return string
     */
    public function getWater()
    {
        return $this->water;
    }

    /**
     * Set muscles
     *
     * @param string $muscles
     *
     * @return User
     */
    public function setMuscles($muscles)
    {
        $this->muscles = $muscles;

        return $this;
    }

    /**
     * Get muscles
     *
     * @return string
     */
    public function getMuscles()
    {
        return $this->muscles;
    }

    /**
     * Set sleepDuration
     *
     * @param string $sleepDuration
     *
     * @return User
     */
    public function setSleepDuration($sleepDuration)
    {
        $this->sleepDuration = $sleepDuration;

        return $this;
    }

    /**
     * Get sleepDuration
     *
     * @return string
     */
    public function getSleepDuration()
    {
        return $this->sleepDuration;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return User
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
     * @return Sport
     */
    public function setAccount(\Runalyze\Bundle\CoreBundle\Entity\Account $account = null)
    {
        $this->account = $account;

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
}
