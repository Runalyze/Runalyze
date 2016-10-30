<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Swimdata
 *
 * @ORM\Table(name="swimdata", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class Swimdata
{
    /**
     * @var string
     *
     * @ORM\Column(name="stroke", type="text", nullable=true)
     */
    private $stroke;

    /**
     * @var string
     *
     * @ORM\Column(name="stroketype", type="text", nullable=true)
     */
    private $stroketype;

    /**
     * @var integer
     *
     * @ORM\Column(name="pool_length", type="smallint", precision=5, nullable=false, options={"default":0})
     */
    private $poolLength = '0';

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
     * @var \Training
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Training")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activityid", referencedColumnName="id")
     * })
     */
    private $activityid;


}

