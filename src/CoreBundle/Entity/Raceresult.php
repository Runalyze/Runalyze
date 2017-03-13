<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Raceresult
 *
 * @ORM\Table(name="raceresult")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\RaceresultRepository")
 */
class Raceresult
{
    /**
     * @var int [km]
     *
     * @ORM\Column(name="official_distance", type="decimal", precision=6, scale=2, nullable=false)
     */
    private $officialDistance;

    /**
     * @var int [s]
     *
     * @ORM\Column(name="official_time", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $officialTime;

    /**
     * @var boolean
     *
     * @ORM\Column(name="officially_measured", type="boolean", columnDefinition="tinyint unsigned NOT NULL DEFAULT 0")
     */
    private $officiallyMeasured = false;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false, options={"default":""})
     */
    private $name = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="place_total", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $placeTotal;

    /**
     * @var int|null
     *
     * @ORM\Column(name="place_gender", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $placeGender;

    /**
     * @var int|null
     *
     * @ORM\Column(name="place_ageclass", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $placeAgeclass;

    /**
     * @var int|null
     *
     * @ORM\Column(name="participants_total", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $participantsTotal;

    /**
     * @var int|null
     *
     * @ORM\Column(name="participants_gender", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $participantsGender;

    /**
     * @var int|null
     *
     * @ORM\Column(name="participants_ageclass", columnDefinition="mediumint unsigned DEFAULT NULL")
     */
    private $participantsAgeclass;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @var Training
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Training")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activity_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $activity;

    /**
     * @param float $officialDistance [km]
     *
     * @return $this
     */
    public function setOfficialDistance($officialDistance)
    {
        $this->officialDistance = $officialDistance;

        return $this;
    }

    /**
     * @return float [km]
     */
    public function getOfficialDistance()
    {
        return $this->officialDistance;
    }

    /**
     * @param float $officialTime [s]
     *
     * @return $this
     */
    public function setOfficialTime($officialTime)
    {
        $this->officialTime = $officialTime;

        return $this;
    }

    /**
     * @return float [s]
     */
    public function getOfficialTime()
    {
        return $this->officialTime;
    }

    /**
     * @param bool $officiallyMeasured
     *
     * @return $this
     */
    public function setOfficiallyMeasured($officiallyMeasured)
    {
        $this->officiallyMeasured = $officiallyMeasured;

        return $this;
    }

    /**
     * @return bool
     */
    public function getOfficiallyMeasured()
    {
        return $this->officiallyMeasured;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int|null $placeTotal
     *
     * @return $this
     */
    public function setPlaceTotal($placeTotal)
    {
        $this->placeTotal = $placeTotal;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPlaceTotal()
    {
        return $this->placeTotal;
    }

    /**
     * @param int|null $placeGender
     *
     * @return $this
     */
    public function setPlaceGender($placeGender)
    {
        $this->placeGender = $placeGender;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPlaceGender()
    {
        return $this->placeGender;
    }

    /**
     * @param int|null $placeAgeclass
     *
     * @return $this
     */
    public function setPlaceAgeclass($placeAgeclass)
    {
        $this->placeAgeclass = $placeAgeclass;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPlaceAgeclass()
    {
        return $this->placeAgeclass;
    }

    /**
     * @param int|null $participantsTotal
     *
     * @return $this
     */
    public function setParticipantsTotal($participantsTotal)
    {
        $this->participantsTotal = $participantsTotal;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getParticipantsTotal()
    {
        return $this->participantsTotal;
    }

    /**
     * @param int|null $participantsGender
     *
     * @return $this
     */
    public function setParticipantsGender($participantsGender)
    {
        $this->participantsGender = $participantsGender;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getParticipantsGender()
    {
        return $this->participantsGender;
    }

    /**
     * @param int|null $participantsAgeclass
     *
     * @return $this
     */
    public function setParticipantsAgeclass($participantsAgeclass)
    {
        $this->participantsAgeclass = $participantsAgeclass;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getParticipantsAgeclass()
    {
        return $this->participantsAgeclass;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Training $activity
     *
     * @return $this
     */
    public function setActivity(Training $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return Training
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param Training $activity
     *
     * @return $this
     */
    public function fillFromActivity(Training $activity)
    {
        $this->setActivity($activity);
        $this->setAccount($activity->getAccount());
        $this->setOfficialDistance($activity->getDistance());
        $this->setOfficialTime($activity->getS());
        $this->setName($activity->getComment());

        return $this;
    }
}
