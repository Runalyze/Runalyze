<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Route
 *
 * @ORM\Table(name="route", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class Route
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", precsion=10, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="cities", type="string", length=255, nullable=false)
     */
    private $cities = '';

    /**
     * @var string
     *
     * @ORM\Column(name="distance", type="decimal", precision=6, scale=2, nullable=false, options={"unsigned":true})
     */
    private $distance = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="elevation", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevation = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="elevation_up", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevationUp = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="elevation_down", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $elevationDown = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="geohashes", type="text", nullable=true)
     */
    private $geohashes;

    /**
     * @var string
     *
     * @ORM\Column(name="elevations_original", type="text", nullable=true)
     */
    private $elevationsOriginal;

    /**
     * @var string
     *
     * @ORM\Column(name="elevations_corrected", type="text", nullable=true)
     */
    private $elevationsCorrected;

    /**
     * @var string
     *
     * @ORM\Column(name="elevations_source", type="string", length=255, nullable=false, options={"default" = ""})
     */
    private $elevationsSource = '';

    /**
     * @var string
     *
     * @ORM\Column(name="startpoint", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $startpoint;

    /**
     * @var string
     *
     * @ORM\Column(name="endpoint", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $endpoint;

    /**
     * @var string
     *
     * @ORM\Column(name="min", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $min;

    /**
     * @var string
     *
     * @ORM\Column(name="max", type="string", length=10, nullable=true, options={"fixed" = true})
     */
    private $max;

    /**
     * @var boolean
     *
     * @ORM\Column(name="in_routenet", type="boolean", nullable=false, options={"default":0})
     */
    private $inRoutenet = '0';

    /**
     * @var \Account
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $accountid;


}

