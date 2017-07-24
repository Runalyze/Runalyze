<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Swimdata
 *
 * @ORM\Table(name="swimdata")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\SwimdataRepository")
 */
class Swimdata
{
    /**
     * @var array|null
     *
     * @ORM\Column(name="stroke", type="pipe_array", nullable=true)
     */
    private $stroke;

    /**
     * @var array|null
     *
     * @ORM\Column(name="stroketype", type="pipe_array", nullable=true)
     */
    private $stroketype;

    /**
     * @var int [cm]
     *
     * @ORM\Column(name="pool_length", type="smallint", precision=5, nullable=false, options={"unsigned":true, "default":0})
     */
    private $poolLength = 0;

    /**
     * @var Account
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
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
     *   @ORM\JoinColumn(name="activityid", referencedColumnName="id")
     * })
     */
    private $activity;


    /**
     * @param array|null
     * @return $this
     */
    public function setStroke(array $stroke = null)
    {
        $this->stroke = $stroke;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getStroke()
    {
        return $this->stroke;
    }

    /**
     * @param array|null
     * @return $this
     */
    public function setStroketype(array $stroketype = null)
    {
        $this->stroketype = $stroketype;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getStroketype()
    {
        return $this->stroketype;
    }

    /**
     * @param int $poolLength [cm]
     * @return $this
     */
    public function setPoolLength($poolLength)
    {
        $this->poolLength = $poolLength;

        return $this;
    }

    /**
     * @return int [cm]
     */
    public function getPoolLength()
    {
        return $this->poolLength;
    }

    /**
     * @param Account $account
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
}
