<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 *
 * @ORM\Table(name="type", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\TypeRepository")
 */
class Type
{
    /**
     * @var integer
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
     * @var \Runalyze\Bundle\CoreBundle\Entity\Sport
     *
     * @ORM\ManyToOne(targetEntity="Sport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sportid", referencedColumnName="id", nullable=false)
     * })
     */
    private $sport;

    /**
     * @var boolean
     *
     * @ORM\Column(name="short", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $short = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="hr_avg", columnDefinition="tinyint(3) unsigned NOT NULL DEFAULT '100'")
     */
    private $hrAvg = '100';

    /**
     * @var boolean
     *
     * @ORM\Column(name="quality_session", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 0")
     */
    private $qualitySession = '0';

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Type
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
     * Set abbr
     *
     * @param string $abbr
     *
     * @return Type
     */
    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;

        return $this;
    }

    /**
     * Get abbr
     *
     * @return string
     */
    public function getAbbr()
    {
        return $this->abbr;
    }

    /**
     * Set sport
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport $sport
     *
     * @return Type
     */
    public function setSport(\Runalyze\Bundle\CoreBundle\Entity\Sport $sport = null)
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * Get sport
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Sport
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Set short
     *
     * @param boolean $short
     *
     * @return Type
     */
    public function setShort($short)
    {
        $this->short = $short;

        return $this;
    }

    /**
     * Get short
     *
     * @return boolean
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * Set hrAvg
     *
     * @param boolean $hrAvg
     *
     * @return Type
     */
    public function setHrAvg($hrAvg)
    {
        $this->hrAvg = $hrAvg;

        return $this;
    }

    /**
     * Get hrAvg
     *
     * @return boolean
     */
    public function getHrAvg()
    {
        return $this->hrAvg;
    }

    /**
     * Set qualitySession
     *
     * @param boolean $qualitySession
     *
     * @return Type
     */
    public function setQualitySession($qualitySession)
    {
        $this->qualitySession = $qualitySession;

        return $this;
    }

    /**
     * Get qualitySession
     *
     * @return boolean
     */
    public function getQualitySession()
    {
        return $this->qualitySession;
    }

    /**
     * Set account
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     *
     * @return Type
     */
    public function setAccount(\Runalyze\Bundle\CoreBundle\Entity\Account $account = null)
    {
        $this->account= $account;

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
}
