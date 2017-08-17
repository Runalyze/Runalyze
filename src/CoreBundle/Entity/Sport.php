<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Metrics\Velocity\Unit\AbstractPaceUnit;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Profile\Sport\AbstractSport;
use Runalyze\Profile\Sport\ProfileInterface;
use Runalyze\Profile\Sport\SportProfile;

/**
 * Sport
 *
 * @ORM\Table(name="sport", uniqueConstraints={@ORM\UniqueConstraint(name="unique_internal_id", columns={"accountid", "internal_sport_id"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\SportRepository")
 */
class Sport
{
    /**
     * @var int
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
     * @ORM\Column(name="img", type="string", length=100, nullable=false, options={"default":"icons8-Sports-Mode"})
     */
    private $img = 'icons8-Sports-Mode';

    /**
     * @var bool
     *
     * @ORM\Column(name="short", type="boolean", columnDefinition="tinyint unsigned NOT NULL DEFAULT 0")
     */
    private $short = false;

    /**
     * @var int [kcal/h]
     *
     * @ORM\Column(name="kcal", type="smallint", precision=4, nullable=false, options={"unsigned":true, "default":0})
     */
    private $kcal = 0;

    /**
     * @var int [bpm]
     *
     * @ORM\Column(name="HFavg", columnDefinition="tinyint unsigned NOT NULL DEFAULT 120")
     */
    private $hfavg = 120;

    /**
     * @var bool
     *
     * @ORM\Column(name="distances", type="boolean", columnDefinition="tinyint unsigned NOT NULL DEFAULT 1")
     */
    private $distances = true;

    /**
     * @var int see \Runalyze\Metrics\Velocity\Unit\PaceEnum
     *
     * @ORM\Column(name="speed", columnDefinition="tinyint unsigned NOT NULL DEFAULT 6")
     */
    private $speed = 6;

    /**
     * @var bool
     *
     * @ORM\Column(name="power", type="boolean", columnDefinition="tinyint unsigned NOT NULL DEFAULT 0")
     */
    private $power = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="outside", type="boolean", columnDefinition="tinyint unsigned NOT NULL DEFAULT 0")
     */
    private $outside = false;

    /**
     * @var EquipmentType|null
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\EquipmentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="main_equipmenttypeid", referencedColumnName="id")
     * })
     */
    private $mainEquipmenttype;

    /**
     * @var Type|null
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Type")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_typeid", referencedColumnName="id")
     * })
     */
    private $defaultType;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_main", type="boolean", columnDefinition="tinyint unsigned NOT NULL DEFAULT 0")
     */
    private $isMain = false;

    /**
     * @var int|null see \Runalyze\Profile\Sport\SportProfile
     *
     * @ORM\Column(name="internal_sport_id", nullable=true, columnDefinition="tinyint NULL")
     */
    private $internalSportId = null;

    /**
     * @var AbstractSport|null
     */
    private $internalSport = null;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account", inversedBy="sports")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\EquipmentType", mappedBy="sport")
     */
    private $equipmentType;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Training", mappedBy="sport", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $trainings;

    public function __construct()
    {
        $this->equipmentType = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setDataFrom(ProfileInterface $profile)
    {
        $this->setInternalSportId($profile->getInternalProfileEnum());
        $this->setImg($profile->getIconClass());
        $this->setHfavg($profile->getAverageHeartRate());
        $this->setName($profile->getName());
        $this->setDistances($profile->hasDistances());
        $this->setPower($profile->hasPower());
        $this->setOutside($profile->isOutside());
        $this->setSpeed($profile->getPaceUnitEnum());
        $this->setKcal($profile->getCaloriesPerHour());

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $img
     *
     * @return $this
     */
    public function setImg($img)
    {
        $this->img = $img;

        return $this;
    }

    /**
     * @return string
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * @param bool $short
     *
     * @return $this
     */
    public function setShort($short)
    {
        $this->short = $short;

        return $this;
    }

    /**
     * Get short
     * @return bool
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * @param int $kcal [kcal/h]
     *
     * @return $this
     */
    public function setKcal($kcal)
    {
        $this->kcal = $kcal;

        return $this;
    }

    /**
     * @return int [kcal/h]
     */
    public function getKcal()
    {
        return $this->kcal;
    }

    /**
     * @param int $bpm [bpm]
     *
     * @return $this
     */
    public function setHfavg($bpm)
    {
        $this->hfavg = $bpm;

        return $this;
    }

    /**
     * @return int [bpm]
     */
    public function getHfavg()
    {
        return $this->hfavg;
    }

    /**
     * @param bool $distances
     *
     * @return $this
     */
    public function setDistances($distances)
    {
        $this->distances = $distances;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDistances()
    {
        return $this->distances;
    }

    /**
     * @param int $speed see \Runalyze\Metrics\Velocity\Unit\PaceEnum
     *
     * @return $this
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * @return int see \Runalyze\Metrics\Velocity\Unit\PaceEnum
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * @return AbstractPaceUnit
     */
    public function getSpeedUnit()
    {
        return PaceEnum::get($this->speed);
    }

    /**
     * @param bool $power
     *
     * @return $this
     */
    public function setPower($power)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * @param bool $outside
     *
     * @return $this
     */
    public function setOutside($outside)
    {
        $this->outside = $outside;

        return $this;
    }

    /**
     * @return bool
     */
    public function getOutside()
    {
        return $this->outside;
    }

    /**
     * @param EquipmentType|null $mainEquipmenttype
     *
     * @return $this
     */
    public function setMainEquipmenttype(EquipmentType $mainEquipmenttype = null)
    {
        $this->mainEquipmenttype = $mainEquipmenttype;

        return $this;
    }

    /**
     * @return EquipmentType|null
     */
    public function getMainEquipmenttype()
    {
        return $this->mainEquipmenttype;
    }

    /**
     * @param Type $defaultType
     *
     * @return $this
     */
    public function setDefaultType(Type $defaultType = null)
    {
        $this->defaultType = $defaultType;

        return $this;
    }

    /**
     * @return Type|null
     */
    public function getDefaultType()
    {
        return $this->defaultType;
    }

    /**
     * @param bool $isMain
     *
     * @return $this
     */
    public function setIsMain($isMain = true)
    {
        $this->isMain = $isMain;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMain()
    {
        return $this->isMain;
    }

    /**
     * @param int|null $internalSportId see \Runalyze\Profile\Sport\SportProfile
     *
     * @return $this
     */
    public function setInternalSportId($internalSportId)
    {
        $this->internalSportId = $internalSportId;
        $this->internalSport = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasInternalSportId()
    {
        return null !== $this->internalSportId && SportProfile::GENERIC != $this->internalSportId;
    }

    /**
     * @return int|null see \Runalyze\Profile\Sport\SportProfile
     */
    public function getInternalSportId()
    {
        return $this->internalSportId;
    }

    /**
     * @return AbstractSport
     */
    public function getInternalSport()
    {
        if (null === $this->internalSport) {
            $id = null === $this->internalSportId ? SportProfile::GENERIC : $this->internalSportId;

            try {
                $this->internalSport = SportProfile::get($id);
            } catch (\InvalidArgumentException $e) {
                $this->internalSportId = null;
                $this->internalSport = SportProfile::get(SportProfile::GENERIC);
            }
        }

        return $this->internalSport;
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
     * @param EquipmentType $equipmentType
     *
     * @return $this
     */
    public function addEquipmentType(EquipmentType $equipmentType)
    {
        $this->equipmentType[] = $equipmentType;

        return $this;
    }

    /**
     * @param EquipmentType $equipmentType
     */
    public function removeEquipmentType(EquipmentType $equipmentType)
    {
        $this->equipmentType->removeElement($equipmentType);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipmentTypes()
    {
        return $this->equipmentType;
    }

    /**
     * @return bool
     */
    public function hasEquipmentTypes()
    {
        return !$this->equipmentType->isEmpty();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrainings()
    {
        return $this->trainings;
    }
}
