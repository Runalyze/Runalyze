<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Equipment
 *
 * @ORM\Table(name="equipment", indexes={@ORM\Index(name="accountid", columns={"accountid"}), @ORM\Index(name="typeid", columns={"typeid"})})
 * @ORM\Entity
 */
class Equipment
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
     * @ORM\Column(name="notes", type="text", length=255, nullable=false)
     */
    private $notes = '';

    /**
     * @var string
     *
     * @ORM\Column(name="distance", type="decimal", precision=8, scale=2, nullable=false, options={"unsigned":true, "default":"0.00"})
     */
    private $distance = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="time", type="integer", nullable=false, options={"unsigned":true, "default":0})
     */
    private $time = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="additional_km", type="integer", nullable=false, options={"unsigned":true, "default":0})
     */
    private $additionalKm = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_start", type="date", nullable=true)
     */
    private $dateStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="date", nullable=true)
     */
    private $dateEnd;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\EquipmentType
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\EquipmentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeid", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $account;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Training", mappedBy="equipment")
     */
    private $activity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activity = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Equipment
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
     * Set notes
     *
     * @param string $notes
     *
     * @return Equipment
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
     * Set distance
     *
     * @param string $distance
     *
     * @return Equipment
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
     * Set time
     *
     * @param integer $time
     *
     * @return Equipment
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
     * Set additionalKm
     *
     * @param integer $additionalKm
     *
     * @return Equipment
     */
    public function setAdditionalKm($additionalKm)
    {
        $this->additionalKm = $additionalKm;

        return $this;
    }

    /**
     * Get additionalKm
     *
     * @return integer
     */
    public function getAdditionalKm()
    {
        return $this->additionalKm;
    }

    /**
     * Set dateStart
     *
     * @param \DateTime $dateStart
     *
     * @return Equipment
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     *
     * @return Equipment
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set type
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\EquipmentType $type
     *
     * @return Equipment
     */
    public function setType(\Runalyze\Bundle\CoreBundle\Entity\EquipmentType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\EquipmentType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set account
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     *
     * @return Equipment
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
     * Add activity
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Training $activity
     *
     * @return Equipment
     */
    public function addActivity(\Runalyze\Bundle\CoreBundle\Entity\Training $activity)
    {
        $this->activity[] = $activity;

        return $this;
    }

    /**
     * Remove activity
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Training $activity
     */
    public function removeActivity(\Runalyze\Bundle\CoreBundle\Entity\Training $activity)
    {
        $this->activity->removeElement($activity);
    }

    /**
     * Get activity
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
