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
     * @ORM\Column(name="place_total", columnDefinition="mediumint(8) unsigned DEFAULT NULL")
     */
    private $placeTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="place_gender", columnDefinition="mediumint(8) unsigned DEFAULT NULL")
     */
    private $placeGender;

    /**
     * @var integer
     *
     * @ORM\Column(name="place_ageclass", columnDefinition="mediumint(8) unsigned DEFAULT NULL")
     */
    private $placeAgeclass;

    /**
     * @var integer
     *
     * @ORM\Column(name="participants_total", columnDefinition="mediumint(8) unsigned DEFAULT NULL")
     */
    private $participantsTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="participants_gender", columnDefinition="mediumint(8) unsigned DEFAULT NULL")
     */
    private $participantsGender;

    /**
     * @var integer
     *
     * @ORM\Column(name="participants_ageclass", columnDefinition="mediumint(8) unsigned DEFAULT NULL")
     */
    private $participantsAgeclass;

    /**
     * @var \Account
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\Column(name="accountid", type="integer", precision=10, nullable=false, options={"unsigned":true})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $account;

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

    /**
     * Set officialDistance
     *
     * @param string $officialDistance
     *
     * @return Raceresult
     */
    public function setOfficialDistance($officialDistance)
    {
        $this->officialDistance = $officialDistance;

        return $this;
    }

    /**
     * Get officialDistance
     *
     * @return string
     */
    public function getOfficialDistance()
    {
        return $this->officialDistance;
    }

    /**
     * Set officialTime
     *
     * @param string $officialTime
     *
     * @return Raceresult
     */
    public function setOfficialTime($officialTime)
    {
        $this->officialTime = $officialTime;

        return $this;
    }

    /**
     * Get officialTime
     *
     * @return string
     */
    public function getOfficialTime()
    {
        return $this->officialTime;
    }

    /**
     * Set officiallyMeasured
     *
     * @param string $officiallyMeasured
     *
     * @return Raceresult
     */
    public function setOfficiallyMeasured($officiallyMeasured)
    {
        $this->officiallyMeasured = $officiallyMeasured;

        return $this;
    }

    /**
     * Get officiallyMeasured
     *
     * @return string
     */
    public function getOfficiallyMeasured()
    {
        return $this->officiallyMeasured;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Raceresult
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set placeTotal
     *
     * @param string $placeTotal
     *
     * @return Raceresult
     */
    public function setPlaceTotal($placeTotal)
    {
        $this->placeTotal = $placeTotal;

        return $this;
    }

    /**
     * Get placeTotal
     *
     * @return string
     */
    public function getPlaceTotal()
    {
        return $this->placeTotal;
    }

    /**
     * Set placeGender
     *
     * @param string $placeGender
     *
     * @return Raceresult
     */
    public function setPlaceGender($placeGender)
    {
        $this->placeGender = $placeGender;

        return $this;
    }

    /**
     * Get placeGender
     *
     * @return string
     */
    public function getPlaceGender()
    {
        return $this->placeGender;
    }

    /**
     * Set placeAgeclass
     *
     * @param string $placeAgeclass
     *
     * @return Raceresult
     */
    public function setPlaceAgeclass($placeAgeclass)
    {
        $this->placeAgeclass = $placeAgeclass;

        return $this;
    }

    /**
     * Get placeAgeclass
     *
     * @return string
     */
    public function getPlaceAgeclass()
    {
        return $this->placeAgeclass;
    }

    /**
     * Set participantsTotal
     *
     * @param string $participantsTotal
     *
     * @return Raceresult
     */
    public function setParticipantsTotal($participantsTotal)
    {
        $this->participantsTotal = $participantsTotal;

        return $this;
    }

    /**
     * Get participantsTotal
     *
     * @return string
     */
    public function getParticipantsTotal()
    {
        return $this->participantsTotal;
    }

    /**
     * Set participantsGender
     *
     * @param string $participantsGender
     *
     * @return Raceresult
     */
    public function setParticipantsGender($participantsGender)
    {
        $this->participantsGender = $participantsGender;

        return $this;
    }

    /**
     * Get participantsGender
     *
     * @return string
     */
    public function getParticipantsGender()
    {
        return $this->participantsGender;
    }

    /**
     * Set participantsAgeclass
     *
     * @param string $participantsAgeclass
     *
     * @return Raceresult
     */
    public function setParticipantsAgeclass($participantsAgeclass)
    {
        $this->participantsAgeclass = $participantsAgeclass;

        return $this;
    }

    /**
     * Get participantsAgeclass
     *
     * @return string
     */
    public function getParticipantsAgeclass()
    {
        return $this->participantsAgeclass;
    }

    /**
     * Set account
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     *
     * @return Raceresult
     */
    public function setAccount(\Runalyze\Bundle\CoreBundle\Entity\Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set activity
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Training $account
     *
     * @return Raceresult
     */
    public function setActivity(\Runalyze\Bundle\CoreBundle\Entity\Training $activity = null)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Training
     */
    public function getActivity()
    {
        return $this->activity;
    }

}

