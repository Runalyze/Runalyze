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
     * @ORM\Column(name="id", type="integer", nullable=false)
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
     * @ORM\Column(name="img", type="string", length=100, nullable=false)
     */
    private $img = 'unknown.gif';

    /**
     * @var boolean
     *
     * @ORM\Column(name="short", type="boolean", nullable=false)
     */
    private $short = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="kcal", type="smallint", nullable=false)
     */
    private $kcal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="HFavg", type="smallint", nullable=false)
     */
    private $hfavg = '120';

    /**
     * @var boolean
     *
     * @ORM\Column(name="distances", type="boolean", nullable=false)
     */
    private $distances = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="speed", type="string", length=10, nullable=false)
     */
    private $speed = 'min/km';

    /**
     * @var boolean
     *
     * @ORM\Column(name="power", type="boolean", nullable=false)
     */
    private $power = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="outside", type="boolean", nullable=false)
     */
    private $outside = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="main_equipmenttypeid", type="integer", nullable=false)
     */
    private $mainEquipmenttypeid = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="default_typeid", type="integer", nullable=true)
     */
    private $defaultTypeid;

    /**
     * @var integer
     *
     * @ORM\Column(name="accountid", type="integer", nullable=false)
     */
    private $accountid;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="EquipmentType", inversedBy="sportid")
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

}

