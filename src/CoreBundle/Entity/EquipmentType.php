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
     * @var boolean
     *
     * @ORM\Column(name="input", type="boolean", nullable=false)
     */
    private $input = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="max_km", type="integer", nullable=false)
     */
    private $maxKm = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="max_time", type="integer", nullable=false)
     */
    private $maxTime = '0';

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
     * @ORM\ManyToMany(targetEntity="Sport", mappedBy="equipmentTypeid")
     */
    private $sportid;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sportid = new \Doctrine\Common\Collections\ArrayCollection();
    }

}

