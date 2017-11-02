<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Profile\View\DataBrowserRowProfile;

/**
 * Type
 *
 * @ORM\Table(name="type")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\TypeRepository")
 */
class Type
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
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
     * @ORM\Column(name="abbr", type="string", length=5, nullable=false, options={"default":""})
     */
    private $abbr = '';

    /**
     * @var Sport
     *
     * @ORM\ManyToOne(targetEntity="Sport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sportid", referencedColumnName="id", nullable=false)
     * })
     */
    private $sport;

    /**
     * @var int
     *
     * @ORM\Column(name="short", type="integer", columnDefinition="tinyint unsigned NOT NULL DEFAULT 2")
     *
     * @see \Runalyze\Profile\View\DataBrowserRowProfile
     */
    private $displayMode = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="hr_avg", columnDefinition="tinyint unsigned NOT NULL DEFAULT '100'")
     */
    private $hrAvg = 100;

    /**
     * @var bool
     *
     * @ORM\Column(name="quality_session", type="boolean", columnDefinition="tinyint unsigned NOT NULL DEFAULT 0")
     */
    private $qualitySession = false;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Training", mappedBy="type", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $trainings;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account", inversedBy="activityTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param string $abbr
     *
     * @return $this
     */
    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;

        return $this;
    }

    /**
     * @return string
     */
    public function getAbbr()
    {
        return $this->abbr;
    }

    /**
     * @param Sport $sport
     *
     * @return $this
     */
    public function setSport(Sport $sport)
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * @return Sport
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * @param int $mode see \Runalyze\Profile\View\DataBrowserRowProfile
     *
     * @return $this
     */
    public function setDisplayMode($mode)
    {
        $this->displayMode = $mode;

        return $this;
    }

    /**
     * @return int see \Runalyze\Profile\View\DataBrowserRowProfile
     */
    public function getDisplayMode()
    {
        return $this->displayMode;
    }

    /**
     * @return bool
     */
    public function showsCompleteRow()
    {
        if ($this->inheritsDisplayMode()) {
            return !$this->sport->getShort();
        }

        return DataBrowserRowProfile::COMPLETE_ROW == $this->displayMode;
    }

    /**
     * @return bool
     */
    public function showsOnlyIcon()
    {
        if ($this->inheritsDisplayMode()) {
            return $this->sport->getShort();
        }

        return DataBrowserRowProfile::ONLY_ICON == $this->displayMode;
    }

    /**
     * @return bool
     */
    public function inheritsDisplayMode()
    {
        return DataBrowserRowProfile::INHERIT_FROM_PARENT == $this->displayMode;
    }

    /**
     * @param int $hrAvg [bpm]
     *
     * @return $this
     */
    public function setHrAvg($hrAvg)
    {
        $this->hrAvg = $hrAvg;

        return $this;
    }

    /**
     * @return int $hrAvg [bpm]
     */
    public function getHrAvg()
    {
        return $this->hrAvg;
    }

    /**
     * @param bool $qualitySession
     *
     * @return $this
     */
    public function setQualitySession($qualitySession)
    {
        $this->qualitySession = $qualitySession;

        return $this;
    }

    /**
     * @return bool
     */
    public function getQualitySession()
    {
        return $this->qualitySession;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account= $account;

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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrainings()
    {
        return $this->trainings;
    }
}
