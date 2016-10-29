<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user", indexes={@ORM\Index(name="time", columns={"accountid", "time"})})
 * @ORM\Entity
 */
class User
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
     * @var integer
     *
     * @ORM\Column(name="time", type="integer", nullable=false)
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="decimal", precision=5, scale=2, nullable=true)
     */
    private $weight;

    /**
     * @var integer
     *
     * @ORM\Column(name="pulse_rest", type="smallint", nullable=true)
     */
    private $pulseRest;

    /**
     * @var integer
     *
     * @ORM\Column(name="pulse_max", type="smallint", nullable=true)
     */
    private $pulseMax;

    /**
     * @var string
     *
     * @ORM\Column(name="fat", type="decimal", precision=3, scale=1, nullable=true)
     */
    private $fat;

    /**
     * @var string
     *
     * @ORM\Column(name="water", type="decimal", precision=3, scale=1, nullable=true)
     */
    private $water;

    /**
     * @var string
     *
     * @ORM\Column(name="muscles", type="decimal", precision=3, scale=1, nullable=true)
     */
    private $muscles;

    /**
     * @var integer
     *
     * @ORM\Column(name="sleep_duration", type="smallint", nullable=true)
     */
    private $sleepDuration;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=65535, nullable=true)
     */
    private $notes;

    /**
     * @var integer
     *
     * @ORM\Column(name="accountid", type="integer", nullable=false)
     */
    private $accountid;


}

