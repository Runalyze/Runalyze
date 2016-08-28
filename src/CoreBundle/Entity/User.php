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
     * @ORM\Column(name="weight", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $weight = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="pulse_rest", type="smallint", nullable=false)
     */
    private $pulseRest = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="pulse_max", type="smallint", nullable=false)
     */
    private $pulseMax = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="fat", type="decimal", precision=3, scale=1, nullable=false)
     */
    private $fat = '0.0';

    /**
     * @var string
     *
     * @ORM\Column(name="water", type="decimal", precision=3, scale=1, nullable=false)
     */
    private $water = '0.0';

    /**
     * @var string
     *
     * @ORM\Column(name="muscles", type="decimal", precision=3, scale=1, nullable=false)
     */
    private $muscles = '0.0';

    /**
     * @var integer
     *
     * @ORM\Column(name="sleep_duration", type="smallint", nullable=false)
     */
    private $sleepDuration = '0';

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

