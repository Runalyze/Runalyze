<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Sessions
 *
 * @ORM\Table(name="sessions")
 * @ORM\Entity
 */
class Sessions
{
    /**
     * @var binary
     *
     * @ORM\Column(name="sess_id", type="binary", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $sessId;

    /**
     * @var string
     *
     * @ORM\Column(name="sess_data", type="blob", length=65535, nullable=false)
     */
    private $sessData;

    /**
     * @var integer
     *
     * @ORM\Column(name="sess_time", type="integer", nullable=false)
     */
    private $sessTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="sess_lifetime", type="integer", nullable=false)
     */
    private $sessLifetime;


}

