<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sport
 *
 * @ORM\Table(name="sport", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class Sport
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
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
     * @var string
     *
     * @ORM\Column(name="img", type="string", length=100, nullable=false, options={"default":"unknown.gif"})
     */
    private $img = 'unknown.gif';

    /**
     * @var boolean
     *
     * @ORM\Column(name="short", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $short = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="kcal", type="smallint", precision=4, nullable=false, options={"unsigned":true, "default":0})
     */
    private $kcal = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="HFavg", columnDefinition="tinyint(3) unsigned NOT NULL DEFAULT 120")
     */
    private $hfavg = '120';

    /**
     * @var boolean
     *
     * @ORM\Column(name="distances", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 1")
     */
    private $distances;

    /**
     * @var string
     *
     * @ORM\Column(name="speed", type="string", length=10, nullable=false, options={"default":"min/km"})
     */
    private $speed = 'min/km';

    /**
     * @var boolean
     *
     * @ORM\Column(name="power", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $power = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="outside", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $outside = '0';

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\EquipmentType
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\EquipmentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="main_equipmenttypeid", referencedColumnName="id")
     * })
     */
    private $mainEquipmenttype;

    /**
     * @var integer
     *
     * @ORM\Column(name="default_typeid", type="integer", nullable=true, options={"unsigned":true})
     */
    private $defaultType;

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
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\EquipmentType", inversedBy="sport")
     * @ORM\JoinTable(name="equipment_sport",
     *   joinColumns={
     *     @ORM\JoinColumn(name="sportid", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="equipment_typeid", referencedColumnName="id")
     *   }
     * )
     */
    private $equipmentType;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->equipmentType = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Sport
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
     * Set img
     *
     * @param string $img
     *
     * @return Sport
     */
    public function setImg($img)
    {
        $this->img = $img;

        return $this;
    }

    /**
     * Get img
     *
     * @return string
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Set short
     *
     * @param boolean $short
     *
     * @return Sport
     */
    public function setShort($short)
    {
        $this->short = $short;

        return $this;
    }

    /**
     * Get short
     *
     * @return boolean
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * Set kcal
     *
     * @param integer $kcal
     *
     * @return Sport
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
     * Set hfavg
     *
     * @param integer $hfavg
     *
     * @return Sport
     */
    public function setHfavg($hfavg)
    {
        $this->hfavg = $hfavg;

        return $this;
    }

    /**
     * Get hfavg
     *
     * @return integer
     */
    public function getHfavg()
    {
        return $this->hfavg;
    }

    /**
     * Set distances
     *
     * @param boolean $distances
     *
     * @return Sport
     */
    public function setDistances($distances)
    {
        $this->distances = $distances;

        return $this;
    }

    /**
     * Get distances
     *
     * @return boolean
     */
    public function getDistances()
    {
        return $this->distances;
    }

    /**
     * Set speed
     *
     * @param string $speed
     *
     * @return Sport
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed
     *
     * @return string
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * Set power
     *
     * @param boolean $power
     *
     * @return Sport
     */
    public function setPower($power)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * Get power
     *
     * @return boolean
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * Set outside
     *
     * @param boolean $outside
     *
     * @return Sport
     */
    public function setOutside($outside)
    {
        $this->outside = $outside;

        return $this;
    }

    /**
     * Get outside
     *
     * @return boolean
     */
    public function getOutside()
    {
        return $this->outside;
    }

    /**
     * Set mainEquipmenttype
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\EquipmentType $mainEquipmenttype
     *
     * @return Sport
     */
    public function setMainEquipmenttype(\Runalyze\Bundle\CoreBundle\Entity\EquipmentType $mainEquipmenttype = null)
    {
        $this->mainEquipmenttype = $mainEquipmenttype;

        return $this;
    }

    /**
     * Get mainEquipmenttype
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\EquipmentType
     */
    public function getMainEquipmenttype()
    {
        return $this->mainEquipmenttype;
    }

    /**
     * Set defaultType
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Type $defaultType
     *
     * @return Sport
     */
    public function setDefaultType(\Runalyze\Bundle\CoreBundle\Entity\Type $defaultType = null)
    {
        $this->defaultType = $defaultType;

        return $this;
    }

    /**
     * Get defaultType
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Type
     */
    public function getDefaultType()
    {
        return $this->defaultType;
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

    /**
     * Add equipmentType
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentType
     *
     * @return Sport
     */
    public function addEquipmentType(\Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentType)
    {
        $this->equipmentType[] = $equipmentType;

        return $this;
    }

    /**
     * Remove equipmentType
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentType
     */
    public function removeEquipmentType(\Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentType)
    {
        $this->equipmentType->removeElement($equipmentType);
    }

    /**
     * Get equipmentType
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipmentType()
    {
        return $this->equipmentType;
    }
}
