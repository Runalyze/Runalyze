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
     * @ORM\Column(name="id", type="integer")
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
     * @var \Runalyze\Bundle\CoreBundle\Entity\Sport
     *
     * @ORM\ManyToOne(targetEntity="Sport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sportid", referencedColumnName="id")
     * })
     */
    private $sportid;

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
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $accountid;

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
     * Set sportid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Sport $sportid
     *
     * @return Type
     */
    public function setSportid(\Runalyze\Bundle\CoreBundle\Entity\Sport $sportid = null)
    {
        $this->sportid = $sportid;

        return $this;
    }

    /**
     * Get sportid
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Sport
     */
    public function getSportid()
    {
        return $this->sportid;
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
     * Set accountid
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $accountid
     *
     * @return Type
     */
    public function setAccountid(\Runalyze\Bundle\CoreBundle\Entity\Account $accountid = null)
    {
        $this->accountid = $accountid;

        return $this;
    }

    /**
     * Get accountid
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Account
     */
    public function getAccountid()
    {
        return $this->accountid;
    }
}
