<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Trackdata
 *
 * @ORM\Table(name="trackdata", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class Trackdata
{
    /**
     * @var string
     *
     * @ORM\Column(name="time", type="text", nullable=true)
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(name="distance", type="text", nullable=true)
     */
    private $distance;

    /**
     * @var string
     *
     * @ORM\Column(name="heartrate", type="text", nullable=true)
     */
    private $heartrate;

    /**
     * @var string
     *
     * @ORM\Column(name="cadence", type="text", nullable=true)
     */
    private $cadence;

    /**
     * @var string
     *
     * @ORM\Column(name="power", type="text", nullable=true)
     */
    private $power;

    /**
     * @var string
     *
     * @ORM\Column(name="temperature", type="text", nullable=true)
     */
    private $temperature;

    /**
     * @var string
     *
     * @ORM\Column(name="groundcontact", type="text", nullable=true)
     */
    private $groundcontact;

    /**
     * @var string
     *
     * @ORM\Column(name="vertical_oscillation", type="text", nullable=true)
     */
    private $verticalOscillation;

    /**
     * @var string
     *
     * @ORM\Column(name="groundcontact_balance", type="text", nullable=true)
     */
    private $groundcontactBalance;

    /**
     * @var string
     *
     * @ORM\Column(name="pauses", type="text", length=65535, nullable=true)
     */
    private $pauses;

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

