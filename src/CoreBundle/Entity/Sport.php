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
     * @ORM\Column(name="short", type="boolean", nullable=false, options={"unsigned":true, "default":0})
     */
    private $short = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="kcal", type="smallint", precision=5, nullable=false, options={"unsigned":true, "default":0})
     */
    private $kcal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="HFavg", type="smallint", nullable=false, options={"unsigned":true, "default":120})
     */
    private $hfavg = '120';

    /**
     * @var boolean
     *
     * @ORM\Column(name="distances", type="boolean", nullable=false, options={"unsigned":true, "default":1})
     */
    private $distances = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="speed", type="string", length=10, nullable=false, options={"default":"min/km"})
     */
    private $speed = 'min/km';

    /**
     * @var boolean
     *
     * @ORM\Column(name="power", type="boolean", nullable=false, options={"unsigned":true, "default":0})
     */
    private $power = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="outside", type="boolean", nullable=false, options={"unsigned":true, "default":0})
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
    private $mainEquipmenttypeid;

    /**
     * @var integer
     *
     * @ORM\Column(name="default_typeid", type="integer", nullable=true, options={"unsigned":true})
     */
    private $defaultTypeid;

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
     * @ORM\ManyToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\EquipmentType", inversedBy="sportid")
     * @ORM\JoinTable(name="equipment_sport",
     *   joinColumns={
     *     @ORM\JoinColumn(name="sportid", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="equipment_typeid", referencedColumnName="id")
     *   }
     * )
     */
    private $equipmentTypeid;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->equipmentTypeid = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set mainEquipmenttypeid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\EquipmentType $mainEquipmenttypeid
     *
     * @return Sport
     */
    public function setMainEquipmenttypeid(\Runalyze\Bundle\CoreBundle\Entity\EquipmentType $mainEquipmenttypeid = null)
    {
        $this->mainEquipmenttypeid = $mainEquipmenttypeid;

        return $this;
    }

    /**
     * Get mainEquipmenttypeid
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\EquipmentType
     */
    public function getMainEquipmenttypeid()
    {
        return $this->mainEquipmenttypeid;
    }

    /**
     * Set defaultTypeid
     *
     * @param integer $defaultTypeid
     *
     * @return Sport
     */
    public function setDefaultTypeid($defaultTypeid)
    {
        $this->defaultTypeid = $defaultTypeid;

        return $this;
    }

    /**
     * Get defaultTypeid
     *
     * @return integer
     */
    public function getDefaultTypeid()
    {
        return $this->defaultTypeid;
    }

    /**
     * Set accountid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $accountid
     *
     * @return Sport
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
     * Add equipmentTypeid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentTypeid
     *
     * @return Sport
     */
    public function addEquipmentTypeid(\Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentTypeid)
    {
        $this->equipmentTypeid[] = $equipmentTypeid;

        return $this;
    }

    /**
     * Remove equipmentTypeid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentTypeid
     */
    public function removeEquipmentTypeid(\Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentTypeid)
    {
        $this->equipmentTypeid->removeElement($equipmentTypeid);
    }

    /**
     * Get equipmentTypeid
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipmentTypeid()
    {
        return $this->equipmentTypeid;
    }
}
