<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EquipmentType
 *
 * @ORM\Table(name="equipment_type", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class EquipmentType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="input", type="boolean", nullable=false)
     */
    private $input = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="max_km", type="integer", nullable=true)
     */
    private $maxKm;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_time", type="integer", nullable=true)
     */
    private $maxTime;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $accountid;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Sport", mappedBy="equipmentTypeid")
     */
    private $sportid;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sportid = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return EquipmentType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set input
     *
     * @param boolean $input
     *
     * @return EquipmentType
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get input
     *
     * @return boolean
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set maxKm
     *
     * @param integer $maxKm
     *
     * @return EquipmentType
     */
    public function setMaxKm($maxKm)
    {
        $this->maxKm = $maxKm;

        return $this;
    }

    /**
     * Get maxKm
     *
     * @return integer
     */
    public function getMaxKm()
    {
        return $this->maxKm;
    }

    /**
     * Set maxTime
     *
     * @param integer $maxTime
     *
     * @return EquipmentType
     */
    public function setMaxTime($maxTime)
    {
        $this->maxTime = $maxTime;

        return $this;
    }

    /**
     * Get maxTime
     *
     * @return integer
     */
    public function getMaxTime()
    {
        return $this->maxTime;
    }

    /**
     * Set accountid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $accountid
     *
     * @return EquipmentType
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
     * Add sportid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport $sportid
     *
     * @return EquipmentType
     */
    public function addSportid(\Runalyze\Bundle\CoreBundle\Entity\Sport $sportid)
    {
        $this->sportid[] = $sportid;

        return $this;
    }

    /**
     * Remove sportid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport $sportid
     */
    public function removeSportid(\Runalyze\Bundle\CoreBundle\Entity\Sport $sportid)
    {
        $this->sportid->removeElement($sportid);
    }

    /**
     * Get sportid
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSportid()
    {
        return $this->sportid;
    }
}

