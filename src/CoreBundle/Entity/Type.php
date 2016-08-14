<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 *
 * @ORM\Table(name="type", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class Type
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
     * @ORM\Column(name="abbr", type="string", length=5, nullable=false)
     */
    private $abbr = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="sportid", type="integer", nullable=false)
     */
    private $sportid = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="short", type="boolean", nullable=false)
     */
    private $short = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="hr_avg", type="boolean", nullable=false)
     */
    private $hrAvg = '100';

    /**
     * @var boolean
     *
     * @ORM\Column(name="quality_session", type="boolean", nullable=false)
     */
    private $qualitySession = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="accountid", type="integer", nullable=false)
     */
    private $accountid;


}

