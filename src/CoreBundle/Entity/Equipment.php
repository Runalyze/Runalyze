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
     * @ORM\Column(name="notes", type="text", length=255, nullable=false)
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="distance", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $distance = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="time", type="integer", nullable=false)
     */
    private $time = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="additional_km", type="integer", nullable=false)
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
     * @var \EquipmentType
     *
     * @ORM\ManyToOne(targetEntity="EquipmentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeid", referencedColumnName="id")
     * })
     */
    private $typeid;

    /**
     * @var \Account
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $accountid;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Training", mappedBy="equipmentid")
     */
    private $activityid;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activityid = new \Doctrine\Common\Collections\ArrayCollection();
    }

}

