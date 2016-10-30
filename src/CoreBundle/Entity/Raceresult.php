<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Raceresult
 *
 * @ORM\Table(name="raceresult", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class Raceresult
{
    /**
     * @var string
     *
     * @ORM\Column(name="official_distance", type="decimal", precision=6, scale=2, nullable=false)
     */
    private $officialDistance;

    /**
     * @var string
     *
     * @ORM\Column(name="official_time", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $officialTime;

    /**
     * @var boolean
     *
     * @ORM\Column(name="officially_measured", type="boolean", nullable=false, options={"unsigned":true, "default":0})
     */
    private $officiallyMeasured = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false, options={"default":""})
     */
    private $name = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="place_total", type="integer", precision=8, nullable=true, options={"unsigned":true})
     */
    private $placeTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="place_gender", type="integer", precision=8, nullable=true, options={"unsigned":true})
     */
    private $placeGender;

    /**
     * @var integer
     *
     * @ORM\Column(name="place_ageclass", type="integer", precision=8, nullable=true, options={"unsigned":true})
     */
    private $placeAgeclass;

    /**
     * @var integer
     *
     * @ORM\Column(name="participants_total", type="integer", precision=8, nullable=true, options={"unsigned":true})
     */
    private $participantsTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="participants_gender", type="integer", precision=8, nullable=true, options={"unsigned":true})
     */
    private $participantsGender;

    /**
     * @var integer
     *
     * @ORM\Column(name="participants_ageclass", type="integer", precision=8, nullable=true, options={"unsigned":true})
     */
    private $participantsAgeclass;

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
     *   @ORM\JoinColumn(name="activity_id", referencedColumnName="id")
     * })
     */
    private $activity;


}

