<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EquipmentType
 *
 * @ORM\Table(name="equipment_type")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository")
 */
class EquipmentType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", precision=10, nullable=false, options={"unsigned":true})
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
     * @ORM\Column(name="input", columnDefinition="tinyint unsigned NOT NULL DEFAULT 0")
     */
    private $input;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_km", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $maxKm;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_time", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $maxTime;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account", inversedBy="equipmentTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Sport", inversedBy="equipmentType")
     * @ORM\JoinTable(name="equipment_sport",
     *   joinColumns={
     *     @ORM\JoinColumn(name="equipment_typeid", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="sportid", referencedColumnName="id")
     *   }
     * )
     */
    private $sport;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sport = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set account
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     *
     * @return EquipmentType
     */
    public function setAccount(Account $account = null)
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
     * Add sport
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport $sport
     *
     * @return EquipmentType
     */
    public function addSport(Sport $sport)
    {
        $this->sport[] = $sport;

        return $this;
    }

    /**
     * Remove sport
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport $sport
     */
    public function removeSport(Sport $sport)
    {
        $this->sport->removeElement($sport);
    }

    /**
     * Get sport
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSport()
    {
        return $this->sport;
    }
}
